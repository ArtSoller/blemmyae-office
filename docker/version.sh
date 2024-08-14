version=$(grep -i "wp_version = " wp-includes/version.php | cut -d "'" -f 2)
composer=$(grep -i "\"johnpbloch/wordpress-core\"" composer.json | cut -d "\"" -f 4)
if [ "$version" != "$composer" ]; then
    echo "Error: Installed WP version differs from WP version in composer.json"
    exit 1
fi
