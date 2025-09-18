<?php

const ZYNLE_API_ID      = '15fff16e-8a52-4905-b798-90f573e345cd'; //api id
const ZYNLE_MERCHANT_ID = 'MEC01012'; //merchant id
const ZYNLE_SECRET      = 'f4917aa7-8508-4e46-bc57-d703fad5ca78'; //secret for key generation

// Some samples show an Authorization header with base64(email:password):
const ZYNLE_AUTH_EMAIL    = 'sandbox@example.com';
const ZYNLE_AUTH_PASSWORD = 'password123';

// Use TEST base URL while you develop (from the PDF)
const ZYNLE_BASE_URL = 'http://api.zynlemoney.com:8070/zynlepaytest/zpay/api';

// const ZYNLE_BASE_URL = 'https://api.zynlemoney.com:8060/zynlepay/zpay/api';

// Helper to build the `key` using secret + request_id (per doc)
function zynle_compute_key(string $request_id): string {
  return base64_encode(sha1(ZYNLE_SECRET . $request_id));
}

function zynle_auth_header(): string {
  return 'Authorization: ' . base64_encode(ZYNLE_AUTH_EMAIL . ':' . ZYNLE_AUTH_PASSWORD);
}
