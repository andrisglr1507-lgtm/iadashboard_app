import os

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\ui\auth\login_screen.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Modify the Base URL logic so it handles :8000 correctly
content = content.replace(
    "ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/classic-dashboard/api');",
    """
                      // Perbaiki Base URL handling untuk Local Server
                      if (ip.contains(':8000')) {
                        ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/api');
                      } else {
                        ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/classic-dashboard/api'); // atau sesuaikan dengan config Laragon
                      }
"""
)

# Improve error handling in the listener
content = content.replace(
    """        error: (error, _) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(error.toString())),
          );
        },""",
    """        error: (error, _) {
          String errMsg = error.toString();
          if (errMsg.contains('SocketException') || errMsg.contains('Connection refused')) {
            errMsg = 'Gagal koneksi ke server. Pastikan IP dan Port benar.';
          } else if (errMsg.contains('401')) {
            errMsg = 'Username atau password salah!';
          }
          
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(errMsg),
              backgroundColor: Colors.red,
            ),
          );
        },"""
)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("File patched successfully!")
