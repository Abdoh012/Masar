# MASAR Backend - AI Instructions

## Project
MASAR backend API.

## Stack
- PHP
- MySQL
- REST API
- JWT authentication
- Local environment: Laragon

## Scope
This repository is backend only.
Do not modify frontend code.

## Architecture
Follow the existing architecture:
- routes/
- app/modules/
- controllers
- services
- repositories
- validators
- middleware
- database/
- postman/

Before changing code, inspect the existing implementation and follow its patterns.

## API
Base URL:
http://localhost/masar-backend/api/v1

Do not invent new endpoints when an existing endpoint already provides the required functionality.

## Database
Do not modify database schema directly if the project uses migrations.
Create/update migrations when schema changes are required.

## Authentication
Authentication uses JWT.

Security rules:
- Never expose password_hash.
- Never expose secrets.
- Never log JWTs, passwords, client secrets, refresh tokens, or OAuth authorization codes.
- OAuth state must remain server-side and single-use.
- Never weaken CSRF/OAuth state validation just to make a test pass.

## Coding Rules
- Make the smallest change necessary.
- Do not refactor unrelated code.
- Do not rename files/functions unless required.
- Reuse existing helpers/services/repositories.
- Follow existing naming conventions.
- Keep backward compatibility unless explicitly asked to break it.

## Testing
After changes:
1. Run PHP syntax checks on changed files.
2. Run focused tests for the changed feature.
3. Do not scan huge log files.
4. Do not dump entire files/logs unnecessarily.
5. If tests fail, inspect only the relevant files and relevant log lines.

## Important
If the requested behavior is already working, do not modify the implementation.
Explain why it works and provide the correct testing procedure instead.

Before finishing:
- summarize changed files
- summarize tests
- report remaining issues
- do not claim success without actually testing