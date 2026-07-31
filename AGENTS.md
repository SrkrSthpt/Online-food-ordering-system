# Bitezy — Agent Workflow Guide

> This file is the **mandatory workflow contract** for every AI agent working in this
> repository (opencode, Cursor, Copilot, etc.). Agents MUST read this file before
> starting any task and MUST follow it on every iteration.

## Purpose

This repository uses a **branch-per-iteration** workflow:

- Every change an agent makes must be committed and pushed to its **own branch** at
  the end of each iteration.
- **Branches are the recovery mechanism** — they are never deleted, and any previous
  state can be recovered by checking out the relevant branch.

## Repository Info

- Project: Bitezy (online food ordering system)
- Remote: `https://github.com/SrkrSthpt/Online-food-ordering-system`
- Baseline branch: `main`

## Branch Rules

1. `main` is the stable baseline. **Never commit directly to `main`.**
2. Every time a new feature is introduced or worked on, a NEW branch is created for it
   with the naming pattern `feature/<short-description>` (e.g.
   `feature/fix-cart-increment`, `feature/rupee-currency`,
   `feature/pending-payment-orders`).
3. **One branch per feature/unit of work.** Do not reuse an old branch for new work.
4. **All pulls and pushes are done on the feature branch** — never push directly to
   `main` and never work on `main`.
5. **Never delete branches.** Every branch is kept so any feature/iteration can be
   recovered.
6. Never force-push and never rewrite history on `main` or shared branches.

## After EVERY Iteration — Commit & Push Checklist

1. Review the changes: `git status` and `git diff`.
2. Create / switch to the iteration branch:
   `git checkout -b feature/<short-description>`
3. Stage only the intended files. **Never commit secrets** (database passwords, API
   keys, credentials).
4. Write a concise, descriptive commit message that states what changed and why.
5. Commit.
6. Push the branch to the remote:
   `git push -u origin feature/<short-description>`
7. Leave the branch open (do NOT merge or delete it) so it remains available for
   recovery.

## Recovery

- To restore a previous iteration's state: `git checkout feature/<description>` — that
  branch holds that iteration's exact code.
- To compare iterations: `git diff main..feature/<description>`.
- If `main` is ever broken, check out the last known-good feature branch and recreate
  `main` from it.

## Starting New Work

1. Ensure you are on the latest baseline: `git checkout main`, then `git pull origin main`.
2. Create your new branch: `git checkout -b feature/<short-description>`.
3. Do the work, then follow the Commit & Push Checklist above before finishing.

## Environment

- Database credentials are read from `.env` at the project root (see `.env.example`
  for the format). `.env` is **gitignored — never commit or push it**.
- `config.php` loads `.env` and falls back to localhost defaults when the file is
  missing.
