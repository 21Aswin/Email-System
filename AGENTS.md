# Agent Security & Privacy Guidelines

## STRICT CONFIDENTIALITY & RESTRICTED FILES:
1. **NEVER read, inspect, print, cat, grep, or display contents of `.env` files** (`.env`, `.env.local`, `.env.production`, or any credentials file).
2. If environment variable names or configurations are needed, **ONLY refer to `.env.example`**, which contains non-sensitive placeholder variables.
3. **NEVER access, print, or output private keys, certificates, or tokens** (`*.key`, `*.pem`, `id_rsa*`, `credentials.json`, `token.json`).
4. **NEVER disclose or echo real user passwords, database passwords, or SMTP secrets** in any tool output, log, or conversation response.
