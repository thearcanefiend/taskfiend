## About Task Fiend
This is a vibe-coded to-do list software that is a lot like many of the other open source task lists out there. None of them were *perfect* and watching machines program is fun, so I asked Claude to code one for me.

Because I designed this for my own devious purposes, I assumed there would be two or three user. It would probably fine for more than that, but it's not designed to scale. It assumes at least one user is technical enough to run this on a server and run scripts at the command line.

### Admin-Like Functions
#### Create/Disable users
There is no admin UI. Here are the commands to do admin-like things:
- Create a user: `php artisan user:create {email} {name} {password}`
- Toggle whether a user is enabled or disabled: `php artisan user:toggle {email}`

A user who is disabled is unable to log in, but their tasks remain present. If they're re-enabled, their tasks are still waiting for them. Tasks they've assigned to other user(s) are still available to those other user(s).

#### API Keys
- Create an API key: `php artisan apikey:create {email}` - this will only print the API key the one time, so note the value immediately.
- Disable an API key: `php artisan apikey:invalidate {key}`

An invalidated API key cannot be re-validated.

