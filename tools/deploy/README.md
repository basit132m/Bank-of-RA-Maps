# Deploying without copying files by hand

Three things used to need doing by hand for every change: paste the code into
the theme, create any new Page in WordPress, and add it to a menu. This folder
replaces all three.

Once it is set up, the whole process is: a change is pushed to GitHub, and the
live site has it about forty seconds later. Nothing to copy, nothing to click.

---

## What you set up once

### 1. Turn on SSH (about two minutes)

1. cPanel → **Manage Shell** (it is under "Exclusive for Namecheap Customers")
2. Switch it **ON**
3. cPanel → **SSH Access** → **Manage SSH Keys** → **Generate a New Key**
   * Key name: `github-deploy`
   * Leave the password empty — a key with a passphrase cannot be used by an
     automated deploy
4. Back on the SSH Keys screen, find `github-deploy` and click **Manage** →
   **Authorize**
5. Click **View/Download** next to the *private* key and copy the whole thing,
   including the `-----BEGIN` and `-----END` lines

Note the hostname while you are there: cPanel → right sidebar → **General
Information** → Server Name, something like `server126.web-hosting.com`.
The port on Namecheap shared hosting is **21098**, not 22.

### 2. Put four values into GitHub

GitHub → your repository → **Settings** → **Secrets and variables** →
**Actions** → **New repository secret**. Add these four:

| Name          | Value                                            |
|---------------|--------------------------------------------------|
| `DEPLOY_HOST` | `server126.web-hosting.com` (yours, from cPanel)  |
| `DEPLOY_USER` | your cPanel username                              |
| `DEPLOY_PORT` | `21098`                                           |
| `DEPLOY_KEY`  | the whole private key you copied in step 1        |

Optionally `DEPLOY_PATH` if your WordPress is not in `public_html` — leave it
out otherwise.

### 3. Do a dry run before anything is written

GitHub → **Actions** → **Deploy to bankofyrmaps.com** → **Run workflow**, with
**Dry run** left ticked.

It connects, compares, and prints every file it *would* change. Nothing on the
server is touched. Read that list. If it wants to overwrite something you
edited directly on the live site, that edit is not in this repository yet —
tell me and I will pull it in first.

Once the list looks right, that is the setup finished.

---

## From then on

**Theme and plugin changes** deploy themselves on every push. Nothing to run.

**WordPress-side setup** — creating pages, wiring the menu — is one command,
run from your own machine (PowerShell on Windows has `ssh` built in):

```
ssh -p 21098 USER@SERVER 'bash -s' < tools/deploy/wp-setup.sh
```

It creates `/about/`, `/community/`, `/guides/` and `/map-editor/` if they do
not exist, assigns a Primary menu if there is not one, and adds each page to
it. Everything is checked before it is done, so running it twice changes
nothing the second time. It never deletes a page, a menu or a menu item.

To add a page later, add one `slug|Title` line to the `PAGES` list in
`wp-setup.sh` and run it again.

---

## If you would rather not use SSH

cPanel has **Git™ Version Control** built in, which needs no keys and no
GitHub secrets:

1. cPanel → **Git™ Version Control** → **Create**
2. Clone URL: `https://github.com/basit132m/Bank-of-RA-Maps.git`
3. Repository path: `repo` (outside `public_html` — it must not be web
   reachable)
4. Afterwards, deploying is **Manage** → **Update from Remote** → **Deploy
   HEAD Commit**

`.cpanel.yml` in the repository root tells it which files go where. This route
is two clicks per deploy rather than none, and it cannot run `wp-setup.sh`, so
pages and menus stay a manual job.

---

## Files here

| File                           | What it is                                        |
|--------------------------------|---------------------------------------------------|
| `../../.github/workflows/deploy.yml` | The automatic deploy, run by GitHub       |
| `wp-setup.sh`                  | Creates the pages and the menu, over SSH          |
| `../../.cpanel.yml`            | Used only by the cPanel Git route                 |

## What is deployed, and what is not

Only `wp-content/themes/astra-child/` and `wp-content/plugins/byrm-maps/` are
copied. WordPress core, your uploads, your database and `wp-config.php` are
never touched by any of this.

`--delete` is on *within those two folders*, so a file removed from the
repository is removed from the site. That is why the first dry run matters: it
is the one chance to see whether the live theme holds anything this repository
does not.
