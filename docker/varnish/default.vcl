## Based on https://docs.varnish-software.com/tutorials/caching-post-requests/#introduction
## and https://github.com/varnish/varnish-modules/blob/b86e658c029c9f7e0b86546ca9128e53378d7902/src/vmod_xkey.vcc#L28
# General docs: https://varnish-cache.org/docs/7.4/index.html
vcl 4.1;

import dynamic;
import std;
import bodyaccess;
import xkey;

# Probe for backend api server
probe api_probe {
    .request =
            "GET /graphql?varnish_vcl&query=%7B__typename%7D HTTP/1.1"
            "Host: varnish"
            "Connection: close";
    .interval = 10s;
    .timeout = 5s;
    .window = 10;
    .threshold = 5;
    .initial = 4;
}

# @todo: Restrict who may purge caches
acl purgers {
    "0.0.0.0"/0;
}

acl ip_v4_only {
    "0.0.0.0"/0;
}

# We won't use any static backend, but Varnish still need a default one
backend default none;
# Exmaple of static backend
#backend default {
#    .host = "api";
#    .port = "8080";
#}

# Set up a dynamic director, @see: https://github.com/nigoroll/libvmod-dynamic/blob/master/src/vmod_dynamic.vcc
sub vcl_init {
    # @see: https://github.com/nigoroll/libvmod-dynamic/blob/077fe6a08a9d185b1ef2d9bb66014b5865c43ab2/src/vmod_dynamic.vcc#L234
    new api = dynamic.director(
        # @todo: Make dynamic and passable as ENV variable
        #port = std.getenv("VARNISH_BACKEND_PORT")
        port = "8080",
        probe = api_probe,
        whitelist = ip_v4_only,
        # Testing low ttl behaviour as well, to avoid timeouts due to unhealthy instance
        ttl = 1m,
        # Graphql Api is very slow from time to time
        connect_timeout = 5s,
        first_byte_timeout = 900s,
        between_bytes_timeout = 900s
    );
}

sub vcl_recv {
    # @todo: Make dynamic and passable as ENV variable
    #set req.backend_hint = api.backend(std.getenv("VARNISH_BACKEND_HOSTNAME"));
    set req.backend_hint = api.backend("api");
    # Force the host header to match the backend (not all backends need it, but example.com does)
    #set req.http.host = std.getenv("VARNISH_BACKEND_HOSTNAME");
    set req.http.host = "api";

    # To cache POST requests
    unset req.http.X-Body-Len;

    # Cache invalidation
    if (req.method == "PURGE") {
        if (client.ip !~ purgers) {
            return (synth(403, "Forbidden"));
        }

        if(!req.http.X-GraphQL-Keys-Purge) {
            return(synth(400,"X-GraphQL-Keys-Purge header is missing"));
        }

        if (req.http.X-GraphQL-Keys-Purge) {
            # @todo: Enable SWR after getting rid of Stellate CDN
            ## Default
            #set req.http.n-gone = xkey.purge(req.http.X-GraphQL-Keys-Purge);
            ## SWR
            set req.http.n-gone = xkey.softpurge(req.http.X-GraphQL-Keys-Purge);

            return (synth(200, "Invalidated "+req.http.n-gone+" objects"));
        } else {
            return (purge);
        }
    }

    # @todo: Fine-grain endpoints and body size
    if (req.method == "POST" && req.url ~ "/graphql$") {
        // Cache bypass header
        if (req.http.x-preview-token) {
            return(pass);
        }

        std.log("Will cache POST for: " + req.http.host + req.url);
        if (std.integer(req.http.content-length, 0) > 5000000) {
            return(synth(413, "The request body size exceeds the limit"));
        }

        if(!std.cache_req_body(5000KB)){
            return(hash);
        }
        set req.http.X-Body-Len = bodyaccess.len_req_body();
        return (hash);
    }
}

sub vcl_hash {
    # To cache POST and PUT requests
    if (req.http.X-Body-Len) {
        bodyaccess.hash_req_body();
    } else {
        hash_data("");
    }
}

sub vcl_backend_fetch {
    if (bereq.http.X-Body-Len) {
        set bereq.method = "POST";
    }
}
