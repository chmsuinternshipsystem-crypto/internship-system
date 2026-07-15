# Setup Guide — For the New Laptop

**Read this whole page first** before doing anything. Each step builds on the previous one.

---

## What You Need to Install First

Go to these websites and download/install these three things before proceeding:

| Software | Where to Get It | Why You Need It |
|----------|----------------|-----------------|
| **XAMPP** (PHP 8.2+) | https://www.apachefriends.org/download.html | Runs PHP + MySQL |
| **Composer** | https://getcomposer.org/download/ | Installs PHP packages |
| **Node.js** (LTS version) | https://nodejs.org/ | Installs JavaScript packages |

**Important:** Install XAMPP in `C:\xampp` (this is the default path — just keep clicking Next).

---

## Two Ways to Run Commands (Pick One)

### Option A: Using Cursor IDE's Built-In Terminal (Recommended)

Since you already use Cursor, this is the easiest way:

1. **Open Cursor** and go to `C:\xampp\htdocs\intern\backend`
   - File → Open Folder → `C:\xampp\htdocs\intern\backend`
2. Press **Ctrl + `** (backtick) to open the terminal panel at the bottom
3. You'll see a terminal tab — this is where you type commands
4. Commands go **one at a time**. Type one, press Enter, wait for it to finish, then type the next.

> ⚠️ **One catch with Cursor's terminal:** The database creation command (Step 3) needs Administrator privileges. Cursor's terminal usually runs as a normal user. For that ONE step, you'll need to use the Windows Command Prompt as admin instead. I'll remind you when we get there.

### Option B: Windows Command Prompt (The Old Way)

If you prefer the classic black window:

1. Press **Windows Key + R** on your keyboard
2. Type `cmd` and press **Enter**
3. A black window appears — this is the Command Prompt

> ⚠️ **When to "Run as Administrator":**
> - **Step 3 only:** Right-click "Command Prompt" in Start → "Run as administrator"
> - **Everything else:** Just open CMD normally

### Either way works — pick whichever you're comfortable with.

---

## Step 1: Extract the Project Folder

You have a ZIP file. Here's how to get it to the right place:

1. **Copy** the ZIP file to `C:\xampp\htdocs\`
   - Open "This PC" → Local Disk (C:) → xampp → htdocs
   - Paste the ZIP file there
2. **Right-click** the ZIP → **Extract All**
3. In the window that pops up, make sure it says: `C:\xampp\htdocs\intern`
4. Click **Extract**
5. When done, you should see a folder at `C:\xampp\htdocs\intern`

> ✅ If you see `C:\xampp\htdocs\intern\README.md` when you open the folder, you're good.

---

## Step 2: Start XAMPP

1. Open the **XAMPP Control Panel** (type "XAMPP" in Start menu and click it)
2. Click the **Start** button next to **Apache** → wait for it to turn green
3. Click the **Start** button next to **MySQL** → wait for it to turn green
4. Keep this window open (you can minimize it)

> ⚠️ **What to do if Apache or MySQL doesn't start:**
> - Apache: Another program might be using port 80. Close Skype or change the port in XAMPP settings.
> - MySQL: Try clicking "Start" again. If it still fails, restart your laptop and try again.

---

## Step 3: Create the Databases

**For this step only**, you need to use the Windows Command Prompt as Administrator. Cursor's terminal won't work here because it doesn't have admin rights for database creation.

Here's how:

1. Press the **Windows Key** (or click Start)
2. Type `cmd`
3. **Right-click** "Command Prompt" → **"Run as administrator"** → click Yes
4. A black CMD window will open

Now copy and paste these commands one at a time, pressing Enter after each:

```cmd
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS internship_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then copy and paste this one, press Enter:

```cmd
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS internship_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

There should be no error messages — just a blank line.

> ✅ If there's no error, the databases were created.

> ⚠️ **If you get an error** like "mysql is not recognized" — double-check XAMPP is installed at `C:\xampp`. If MySQL is running, try closing the CMD window, reopening it as admin, and trying again.

**After this step, you can close this admin CMD window.** From now on, use Cursor's built-in terminal (Ctrl + `) — it's easier and you won't need admin rights again.

---

## Step 4: Install PHP Dependencies (Composer)

**Close the admin CMD window** and open a **new normal CMD** (just type `cmd` in Start, don't right-click).

Type these commands one at a time:

```cmd
cd C:\xampp\htdocs\intern\backend
```

Press Enter. This goes to the project's backend folder.

```cmd
C:\xampp\php\php.exe composer.phar install
```

Press Enter. You'll see lots of text scrolling. Wait for it to finish (may take 2-5 minutes).

> ⚠️ **If you see "composer.phar not found":**
> Don't worry. Type these commands instead:
> ```cmd
> cd C:\xampp\htdocs\intern\backend
> C:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
> C:\xampp\php\php.exe composer-setup.php
> C:\xampp\php\php.exe -r "unlink('composer-setup.php');"
> C:\xampp\php\php.exe composer.phar install
> ```

> ⚠️ **If you see errors about PHP extensions:** Most are fine, but if it says "BCMath extension" or "PDO extension" is missing, you may need to enable them in XAMPP:
> 1. Open `C:\xampp\php\php.ini` in Notepad
> 2. Search for `;extension=bcmath` — remove the `;` at the front
> 3. Search for `;extension=pdo_mysql` — remove the `;` at the front
> 4. Save and close. Restart Apache in XAMPP Control Panel.

---

## Step 5: Set Up the Environment File

Still in the same terminal window:

```cmd
cd C:\xampp\htdocs\intern\backend
```

```cmd
copy .env.example .env
```

Press Enter. You should see: `1 file(s) copied.`

**That's it — you don't need to edit anything.** The defaults already work for XAMPP.

> ⚠️ **If you get "The system cannot find the file specified":** Make sure you typed `cd C:\xampp\htdocs\intern\backend` first.

---

## Step 6: Generate Key, Run Migrations, and Seed

Type each command separately and wait for it to finish:

```cmd
cd C:\xampp\htdocs\intern\backend
```

```cmd
C:\xampp\php\php.exe artisan key:generate
```

You should see: `Application key set successfully.`

```cmd
C:\xampp\php\php.exe artisan migrate
```

You'll see lots of lines with `DONE` at the end. Wait for it to finish.

> ⚠️ **If migrate fails with "Access denied":** The MySQL password might be different on this laptop. Open `C:\xampp\htdocs\intern\backend\.env` in Notepad and check:
> - `DB_USERNAME=root` (usually correct)
> - `DB_PASSWORD=` (leave blank if no password)
> If MySQL has a password, type it after the `=`.

```cmd
C:\xampp\php\php.exe artisan db:seed
```

This creates sample data. Wait for it to finish. You should see no errors.

> ✅ If all three commands succeed, the database is ready.

---

## Step 7: Create Storage Symlink

```cmd
C:\xampp\php\php.exe artisan storage:link
```

You should see: `The [public/storage] link has been connected.`

> ⚠️ **If it says "link already exists":** That's fine — skip this step.

---

## Step 8: Install Philippine Address Data (Provinces/Cities/Barangays)

```cmd
C:\xampp\php\php.exe artisan psgc:seed
```

This fetches address data from the internet. It takes **30-60 seconds**.

> ⚠️ **If it says "No internet connection" or hangs:** Skip this step. The address dropdowns in the Company form won't work, but the rest of the system will. Run it later when you have internet.

---

## Step 9: Install Frontend Files and Build

```cmd
npm install
```

This downloads JavaScript packages. Wait 1-3 minutes. You'll see lots of text.

> ⚠️ **If npm says "not recognized":** Node.js wasn't installed properly. Install it from https://nodejs.org/ (download the LTS version, run the installer).

Then:

```cmd
npm run build
```

Wait 10-30 seconds. The last line should say: `✓ built in X.XXs`

---

## ✅ Step 10: Start the System

You need to keep **two things running** at the same time. In Cursor, you can do this easily with split terminals.

### If You're Using Cursor's Terminal (Easiest):

1. Press **Ctrl + `** to open the terminal panel
2. Click the **+** (plus icon) or the **split icon** ➗ to create a second terminal tab
3. You should now see two terminal tabs side by side

**Terminal Tab 1 — Web Server:**
```cmd
C:\xampp\php\php.exe artisan serve
```
You'll see: `Server running on [http://localhost:8000]`. Leave this running.

**Terminal Tab 2 — Email Queue (for OTP codes):**
Click the other terminal tab, then:
```cmd
C:\xampp\php\php.exe artisan queue:work --tries=3
```
You'll see a blinking cursor or "Processing jobs...". Leave this running too.

> Both terminals run inside Cursor — you can minimize Cursor and they'll keep running.

### If You're Using Windows CMD (Alternative):

Open **two separate CMD windows** and run one command in each:

**Window 1 — Web Server:**
```cmd
cd C:\xampp\htdocs\intern\backend
C:\xampp\php\php.exe artisan serve
```

**Window 2 — Email Queue:**
```cmd
cd C:\xampp\htdocs\intern\backend
C:\xampp\php\php.exe artisan queue:work --tries=3
```

> Keep both windows open. You can minimize them.

---

## Step 11: Open the System

Open your **browser** (Chrome or Edge) and go to:

```
http://localhost:8000
```

You should see the login page with the CHMSU logo and "Streamlining Internship Success" text.

---

## Login Credentials

### Staff (click "Faculty & staff" on login page):

| Role | Email | Password |
|------|-------|----------|
| Instructor | `instructor@chmsu.edu.ph` | `password` |
| Chairperson | `chairperson@chmsu.edu.ph` | `password` |
| Dean | `dean@chmsu.edu.ph` | `password` |

### Students (click "Student" on login page):

| Student Name | Student Number | Password |
|-------------|---------------|----------|
| Dela Cruz, Juan | `20230001` | `20230001` |
| Santos, Maria | `20230002` | `20230002` |

> **⚠️ About Student Login (OTP):**
> Students need a 6-digit code sent to their email to log in. Since this laptop uses "log" mode (no real emails), the code is saved to a file. Here's how to find it:
>
> 1. Open `C:\xampp\htdocs\intern\backend\storage\logs\laravel.log`
> 2. Press **Ctrl + F** and search for **"OTP"**
> 3. You'll see a 6-digit number like `571232` — that's the code
>
> Or run this command in CMD:
> ```cmd
> C:\xampp\php\php.exe -r "$log = file_get_contents('C:\xampp\htdocs\intern\backend\storage\logs\laravel.log'); preg_match('/OTP code: (\d{6})/', $log, $m); echo 'OTP: ' . ($m[1] ?? 'Not found. Try logging in first.') . PHP_EOL;"
> ```

---

## Common Problems & How to Fix Them

| If This Happens | Try This |
|----------------|----------|
| **"php is not recognized"** | Use `C:\xampp\php\php.exe` instead of just `php` |
| **"Target class does not exist"** | `C:\xampp\php\php.exe artisan route:clear` then `C:\xampp\php\php.exe artisan optimize` |
| **Page looks plain (no colors)** | The frontend isn't built. Run: `cd C:\xampp\htdocs\intern\backend` then `npm run build` |
| **White screen / 500 error** | Open `C:\xampp\htdocs\intern\backend\storage\logs\laravel.log` and look at the last lines |
| **"npm is not recognized"** | Install Node.js from https://nodejs.org/ |
| **Port 8000 already in use** | `C:\xampp\php\php.exe artisan serve --port=8001` then go to `http://localhost:8001` |
| **"The page is not loading"** | Make sure Terminal 1 (the web server) is still running. |
| **"Something changed but I don't see it"** | `C:\xampp\php\php.exe artisan view:clear && C:\xampp\php\php.exe artisan config:clear` |
| **MySQL won't start in XAMPP** | Restart your laptop. If it still won't start, check if another app is using port 3306. |

---

## The Short Version (If You Already Know What You're Doing)

1. Start XAMPP → Apache + MySQL
2. Open CMD as admin → create databases
3. `cd C:\xampp\htdocs\intern\backend`
4. `C:\xampp\php\php.exe composer.phar install`
5. `copy .env.example .env`
6. `C:\xampp\php\php.exe artisan key:generate`
7. `C:\xampp\php\php.exe artisan migrate`
8. `C:\xampp\php\php.exe artisan db:seed`
9. `C:\xampp\php\php.exe artisan storage:link`
10. `C:\xampp\php\php.exe artisan psgc:seed` (needs internet)
11. `npm install && npm run build`
12. `C:\xampp\php\php.exe artisan serve` (keep open)
13. Open `http://localhost:8000`
