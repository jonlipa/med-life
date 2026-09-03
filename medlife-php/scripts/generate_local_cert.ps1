param(
    [switch] $Force
)

$ErrorActionPreference = 'Stop'

$certDir = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\certs'))
$rootName = 'Med Life Local Dev Root CA'
$serverName = 'Med Life Local HTTPS'
$rootCertPath = Join-Path $certDir 'medlife-local-root-ca.crt'
$serverPfxPath = Join-Path $certDir 'medlife.local.pfx'
$pfxPassword = 'MedLifeLocalDev#2026!'

if (-not (Test-Path $certDir)) {
    New-Item -ItemType Directory -Path $certDir | Out-Null
}

$rootCert = Get-ChildItem Cert:\CurrentUser\My -ErrorAction SilentlyContinue |
    Where-Object { $_.Subject -eq "CN=$rootName" -and $_.FriendlyName -eq $rootName } |
    Sort-Object NotAfter -Descending |
    Select-Object -First 1

$serverCert = Get-ChildItem Cert:\CurrentUser\My -ErrorAction SilentlyContinue |
    Where-Object { $_.Subject -eq 'CN=127.0.0.1' -and $_.Issuer -eq "CN=$rootName" -and $_.FriendlyName -eq $serverName } |
    Sort-Object NotAfter -Descending |
    Select-Object -First 1

if ($Force -or -not $rootCert) {
    $rootCert = New-SelfSignedCertificate `
        -Type Custom `
        -Subject "CN=$rootName" `
        -FriendlyName $rootName `
        -Provider 'Microsoft Software Key Storage Provider' `
        -KeyAlgorithm RSA `
        -KeyLength 2048 `
        -HashAlgorithm SHA256 `
        -KeyUsage CertSign, CRLSign, DigitalSignature `
        -KeyExportPolicy Exportable `
        -CertStoreLocation Cert:\CurrentUser\My `
        -NotAfter (Get-Date).AddYears(5) `
        -TextExtension @('2.5.29.19={critical}{text}ca=1&pathlength=0')
}

if ($Force -or -not $serverCert) {
    $serverCert = New-SelfSignedCertificate `
        -Type Custom `
        -Subject 'CN=127.0.0.1' `
        -FriendlyName $serverName `
        -Signer $rootCert `
        -Provider 'Microsoft Software Key Storage Provider' `
        -KeyAlgorithm RSA `
        -KeyLength 2048 `
        -HashAlgorithm SHA256 `
        -KeyUsage DigitalSignature, KeyEncipherment `
        -KeyExportPolicy Exportable `
        -CertStoreLocation Cert:\CurrentUser\My `
        -NotAfter (Get-Date).AddYears(2) `
        -TextExtension @(
            '2.5.29.17={text}DNS=localhost&DNS=medlife.local&IPAddress=127.0.0.1',
            '2.5.29.37={text}1.3.6.1.5.5.7.3.1'
        )
}

$rootInTrustedStore = Get-ChildItem Cert:\CurrentUser\Root -ErrorAction SilentlyContinue |
    Where-Object { $_.Thumbprint -eq $rootCert.Thumbprint } |
    Select-Object -First 1

if (-not $rootInTrustedStore) {
    Export-Certificate -Cert $rootCert -FilePath $rootCertPath -Type CERT | Out-Null
    Import-Certificate -FilePath $rootCertPath -CertStoreLocation Cert:\CurrentUser\Root | Out-Null
} elseif ($Force -or -not (Test-Path $rootCertPath)) {
    Export-Certificate -Cert $rootCert -FilePath $rootCertPath -Type CERT | Out-Null
}

if ($Force -or -not (Test-Path $serverPfxPath)) {
    $securePassword = ConvertTo-SecureString -String $pfxPassword -AsPlainText -Force
    Export-PfxCertificate -Cert $serverCert -FilePath $serverPfxPath -Password $securePassword | Out-Null
}

Write-Host "Trusted root: $($rootCert.Thumbprint)"
Write-Host "Server cert:  $($serverCert.Thumbprint)"
Write-Host "PFX:          $serverPfxPath"
