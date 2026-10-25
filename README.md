# Cartzy

<img src="assets/app_icon.png" alt="Cartzy app icon" width="96" align="right">

Cartzy is a shopping app for buyers and delivery riders. Buyers browse products,
add them to a cart and check out; riders sign up with their government ID and
manage pickups, deliveries and earnings. It uses the same accounts and products
as the [Cartzy website](https://cartzyy.shop).

## Download (Android)

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

## For developers

Debug runs use the Laravel backend on your computer. With the phone connected
over USB:

```sh
adb reverse tcp:8000 tcp:8000   # once each time the phone is plugged in
flutter run
```

Release builds use the live server (`https://cartzyy.shop/api`) automatically.
To build the APKs for a release:

```sh
flutter build apk --release --split-per-abi
```

Upload `app-arm64-v8a-release.apk` as **cartzy.apk** and
`app-armeabi-v7a-release.apk` as **cartzy-older-phones.apk**, so the download
links above keep working.
