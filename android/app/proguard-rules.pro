# ML Kit text recognition: only the Latin model is bundled (enough for
# Philippine IDs). The plugin also references the optional Chinese,
# Devanagari, Japanese and Korean models, which aren't included.
-dontwarn com.google.mlkit.vision.text.chinese.**
-dontwarn com.google.mlkit.vision.text.devanagari.**
-dontwarn com.google.mlkit.vision.text.japanese.**
-dontwarn com.google.mlkit.vision.text.korean.**
