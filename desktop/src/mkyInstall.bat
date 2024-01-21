powershell "$s= (New-Object -COM WScript.Shell).CreateShortcut('%userprofile%\Desktop\BitMonky Wallet.lnk');$s.TargetPath='%SystemDrive%\\bitMonky\\mkyWalletSRV.bat';$s.IconLocation='%SystemDrive%\bitMonky\html\passport_icon.ico';$s.Save()"


