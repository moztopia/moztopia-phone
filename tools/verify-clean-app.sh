#!/bin/sh
set -eu

phone_package_dir=$(pwd -P)
phone_app_dir=${1:-"$phone_package_dir/.verification/laravel"}
case "$phone_app_dir" in
    /*) ;;
    *) phone_app_dir="$phone_package_dir/$phone_app_dir" ;;
esac
case "$phone_app_dir/" in
    "$phone_package_dir/.verification/"*) ;;
    "$phone_package_dir/"*)
        printf '%s\n' 'Use .verification/ or a destination outside the package checkout.' >&2
        exit 1
        ;;
esac

if [ -e "$phone_app_dir" ]; then
    printf '%s\n' 'Choose an absent destination for the clean Laravel application.' >&2
    exit 1
fi

composer create-project 'laravel/laravel:^13.0' "$phone_app_dir" --no-interaction --no-scripts --prefer-dist
COMPOSER_ROOT_VERSION=$(cat "$phone_package_dir/VERSION") composer archive --format=tar --dir="$phone_app_dir" --file=phone-source --no-interaction
phone_source_dir="$phone_app_dir/package-source"
mkdir "$phone_source_dir"
tar -xf "$phone_app_dir/phone-source.tar" -C "$phone_source_dir"
php -r '
$path = $argv[1] . "/composer.json";
$manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
$manifest["repositories"] = [["type" => "path", "url" => $argv[2], "options" => ["symlink" => false, "versions" => ["moztopia/phone" => trim(file_get_contents($argv[2] . "/VERSION"))]]]];
file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
' "$phone_app_dir" "$phone_source_dir"

(
    cd "$phone_app_dir"
    composer require 'moztopia/phone:*' --no-interaction --prefer-dist
)

php "$phone_package_dir/tools/clean-app-acceptance.php" "$phone_app_dir"
