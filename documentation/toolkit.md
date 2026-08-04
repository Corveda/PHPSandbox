# Browser toolkit

The toolkit in `toolkit/index.php` is a local development aid for exploring policies and templates. It is not a hosted sandbox service.

Serve the repository through a local PHP web server with dependencies installed, then open the toolkit route. The UI refuses non-loopback clients, uses a session CSRF token for mutations, caps request/upload sizes, restricts template reads to `toolkit/templates/*.json`, escapes diagnostic/generated source, and marks setup code as trusted host `eval`.

`PHPSANDBOX_TOOLKIT_ALLOW_REMOTE=1` disables only the loopback check. If that escape hatch is used, the operator must supply authentication, authorization, TLS, request throttling, and process isolation outside the toolkit. The project does not claim that configuration is safe for hostile users.

The configuration pane discovers boolean options from `PHPSandbox`, including PHP 8.5 switches. Its whitelist/blacklist editors support enum and attribute names plus canonical method, property, and class-constant entries.

Saved templates use schema v4. Loading executable definitions or trusted prepend/append code is an explicit toolkit trust decision; imported templates should be reviewed as source code before use.

