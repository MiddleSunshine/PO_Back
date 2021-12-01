```bash
Usage: brew services [subcommand]

Manage background services with macOS' launchctl(1) daemon manager.

If sudo is passed, operate on /Library/LaunchDaemons (started at boot).
Otherwise, operate on ~/Library/LaunchAgents (started at login).

[sudo] brew services [list]:
    List all managed services for the current user (or root).

[sudo] brew services run (formula|--all):
    Run the service formula without registering to launch at login (or boot).

[sudo] brew services start (formula|--all):
    Start the service formula immediately and register it to launch at login
(or boot).

[sudo] brew services stop (formula|--all):
    Stop the service formula immediately and unregister it from launching at
login (or boot).

[sudo] brew services restart (formula|--all):
    Stop (if necessary) and start the service formula immediately and register
it to launch at login (or boot).

[sudo] brew services cleanup:
    Remove all unused services.

      --file                       Use the plist file from this location to
                                   start or run the service.
      --all                        Run subcommand on all services.
  -d, --debug                      Display any debugging information.
  -q, --quiet                      Make some output more quiet.
  -v, --verbose                    Make some output more verbose.
  -h, --help                       Show this message.
```