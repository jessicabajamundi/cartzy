# Cartzy

<img src="public/images/cartzy-app-icon.png" alt="Cartzy app icon" width="96" align="right">

Cartzy is an online marketplace for buyers, sellers and delivery riders.
Shop on the website at **[cartzyy.shop](https://cartzyy.shop)** or with the
Android app. Both use the same accounts and products.

## Download the Android app

**[⬇ Download Cartzy for Android](https://github.com/jessicabajamundi/cartzy/releases/latest/download/cartzy.apk)**

Works on most Android phones (Android 7.0 or newer). On an older phone, if the
app won't install, use the
[version for older phones](https://github.com/jessicabajamundi/cartzy/releases/latest/download/cartzy-older-phones.apk).
All versions are on the [Releases page](https://github.com/jessicabajamundi/cartzy/releases).

### How to install

1. Open the download link on your phone and download the file.
2. Tap the downloaded file. If Android asks, allow your browser or file manager
   to **install unknown apps**.
3. If Google Play Protect warns that the app is unrecognized, tap
   **More details → Install anyway**. This appears for any app that isn't from
   the Play Store.

To update, download the new version and install it over the old one.

## What's in this repository

| Branch | Contents |
|---|---|
| `main` | The website and the API the app uses (Laravel 12, PHP 8.2) |
| [`tiffany`](https://github.com/jessicabajamundi/cartzy/tree/tiffany) | The mobile app for buyers and riders (Flutter) |

## Running the website locally

```sh
composer install
npm install && npm run build
cp .env.example .env      # then fill in the database and mail settings
php artisan key:generate
php artisan serve
```

The site runs at http://127.0.0.1:8000, and the app's API at
http://127.0.0.1:8000/api. To build a production image instead, see the
`Dockerfile`.
