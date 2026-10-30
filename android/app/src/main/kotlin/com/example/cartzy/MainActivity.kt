package com.example.cartzy

import android.content.Intent
import android.net.Uri
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val CHANNEL = "com.cartzy.app/updater"

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL).setMethodCallHandler { call, result ->
            if (call.method == "openUrl") {
                val url = call.argument<String>("url")
                if (!url.isNullOrEmpty()) {
                    try {
                        val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                        startActivity(intent)
                        result.success(true)
                    } catch (e: Exception) {
                        result.error("ACTIVITY_NOT_FOUND", e.message, null)
                    }
                } else {
                    result.error("BAD_ARGS", "Missing url", null)
                }
            } else {
                result.notImplemented()
            }
        }
    }
}
