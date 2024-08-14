#!/bin/bash
CLOUDFLARE_FILE_PATH=./cloudflare.map;

echo "# Aws Private ELB IPs" > $CLOUDFLARE_FILE_PATH;
echo "## IPv4" >> $CLOUDFLARE_FILE_PATH;
echo "set_real_ip_from 0.0.0.0/0;" >> $CLOUDFLARE_FILE_PATH;
for i in $(seq 0 3); do
    echo "set_real_ip_from 10.0.$i.0/24;" >> $CLOUDFLARE_FILE_PATH;
done

echo "" >> $CLOUDFLARE_FILE_PATH;
echo "real_ip_recursive on;" >> $CLOUDFLARE_FILE_PATH;
