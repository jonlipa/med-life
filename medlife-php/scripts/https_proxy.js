'use strict';

const fs = require('node:fs');
const http = require('node:http');
const https = require('node:https');
const path = require('node:path');

const backendUrl = new URL(process.env.MEDLIFE_BACKEND_URL || 'http://127.0.0.1:8000');
const listenHost = process.env.MEDLIFE_HTTPS_HOST || '127.0.0.1';
const listenPort = Number(process.env.MEDLIFE_HTTPS_PORT || 8443);
const pfxPath = process.env.MEDLIFE_SSL_PFX || path.resolve(__dirname, '..', '..', 'certs', 'medlife.local.pfx');
const pfxPassphrase = process.env.MEDLIFE_SSL_PFX_PASSWORD || 'MedLifeLocalDev#2026!';
const certPath = process.env.MEDLIFE_SSL_CERT || path.resolve(__dirname, '..', '..', 'certs', 'medlife.local.crt');
const keyPath = process.env.MEDLIFE_SSL_KEY || path.resolve(__dirname, '..', '..', 'certs', 'medlife.local.key');

if (backendUrl.protocol !== 'http:') {
  console.error('MEDLIFE_BACKEND_URL must be an http:// URL because this proxy terminates HTTPS locally.');
  process.exit(1);
}

function readTlsFile(filePath, label) {
  if (!fs.existsSync(filePath)) {
    console.error(`Missing ${label}: ${filePath}`);
    process.exit(1);
  }

  return fs.readFileSync(filePath);
}

function tlsOptions() {
  if (fs.existsSync(pfxPath)) {
    return {
      pfx: fs.readFileSync(pfxPath),
      passphrase: pfxPassphrase,
    };
  }

  return {
    cert: readTlsFile(certPath, 'SSL certificate'),
    key: readTlsFile(keyPath, 'SSL private key'),
  };
}

const server = https.createServer(tlsOptions(), (request, response) => {
  const forwardedFor = [
    request.headers['x-forwarded-for'],
    request.socket.remoteAddress,
  ].filter(Boolean).join(', ');

  const proxyRequest = http.request({
    protocol: backendUrl.protocol,
    hostname: backendUrl.hostname,
    port: backendUrl.port || 80,
    method: request.method,
    path: request.url,
    headers: {
      ...request.headers,
      host: backendUrl.host,
      'x-forwarded-for': forwardedFor,
      'x-forwarded-host': request.headers.host || `${listenHost}:${listenPort}`,
      'x-forwarded-proto': 'https',
      'x-forwarded-ssl': 'on',
    },
  }, (proxyResponse) => {
    response.writeHead(proxyResponse.statusCode || 502, proxyResponse.headers);
    proxyResponse.pipe(response);
  });

  proxyRequest.on('error', (error) => {
    if (!response.headersSent) {
      response.writeHead(502, { 'content-type': 'text/plain; charset=utf-8' });
    }

    response.end(`Med Life HTTPS proxy: backend is unavailable.\n${error.message}\n`);
  });

  request.pipe(proxyRequest);
});

server.listen(listenPort, listenHost, () => {
  console.log(`Med Life HTTPS proxy: https://${listenHost}:${listenPort}`);
  console.log(`Forwarding to: ${backendUrl.href}`);
  console.log(`Certificate: ${fs.existsSync(pfxPath) ? pfxPath : certPath}`);
});
