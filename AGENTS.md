## Agent skills

### Response style

Use the installed `caveman` skill by default for work in this repository. Keep replies concise and clear while preserving technical details. If the user says "stop caveman" or "normal mode", stop using it for that conversation.

### Git delivery

Commit each completed change in this repository after checking it. Stage only files belonging to that change; leave unrelated worktree changes untouched. Create a pull request only when the user explicitly asks for a PR.

### Anti Slop

For every code, design, documentation, or pull request change in this repository, load the `antislop` core skill and apply its relevant checks during the work. For website and UI design or implementation, also use `design-taste-frontend`, `antislop-ui`, `antislop-human`, and `antislop-layoutmobile`. For user-facing text and pull request descriptions, use `antislop-copywriting`; for code comments, use `antislop-code`. Check each changed artifact before delivery. Apply each rule only to the artifact it governs; routine shell commands do not require a visual-design review.

### Issue tracker

Issues and specs are tracked in GitHub Issues for `von-ai/ojs-rpl-backend`. See `docs/agents/issue-tracker.md`.

### Triage labels

Use the five default triage labels. See `docs/agents/triage-labels.md`.

### Domain docs

Use a single-context layout for domain docs. See `docs/agents/domain.md`.
