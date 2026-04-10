# ================================================
# Script Upload Otomatis ke GitHub - PrintKantor
# ================================================

Clear-Host
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "   PrintKantor - Upload to GitHub" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Masuk ke folder project
Set-Location "C:\xampp\htdocs\printkantor"

Write-Host "📂 Masuk ke folder project..." -ForegroundColor Yellow

# Tambahkan semua perubahan
git add .
Write-Host "✅ Semua file ditambahkan" -ForegroundColor Green

# Commit
$commitMessage = Read-Host "Masukkan pesan commit (contoh: Update kasir page)"
if ([string]::IsNullOrWhiteSpace($commitMessage)) {
    $commitMessage = "Update project - $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
}

git commit -m "$commitMessage"
Write-Host "✅ Commit berhasil dengan pesan: $commitMessage" -ForegroundColor Green

# Push ke GitHub
Write-Host "🚀 Sedang mengupload ke GitHub..." -ForegroundColor Yellow
git push

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "🎉 BERHASIL! Project sudah terupload ke GitHub" -ForegroundColor Green
    Write-Host "🔗 Link: https://github.com/zorromugirawa-lang/printkantor" -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "❌ Push gagal. Cek koneksi atau token GitHub." -ForegroundColor Red
}

Write-Host ""
Write-Host "Tekan tombol apa saja untuk keluar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")