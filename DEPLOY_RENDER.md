# Render.com Deployment Guide - Novalyx Cloud Webmail

This repository is pre-configured for one-click deployment on **[Render](https://render.com/)** using Docker.

---

## 🚀 Quick Step-by-Step Instructions

### Step 1: Connect GitHub Repository to Render
1. Log in to [dashboard.render.com](https://dashboard.render.com/).
2. Click **New +** and select **Web Service** (or **Blueprint** if using `render.yaml`).
3. Connect your GitHub repository:
   ```
   https://github.com/21Aswin/Email-System.git
   ```
4. Choose **Docker** as the Runtime (Render will automatically detect the root `Dockerfile`).
5. Select the **Free** instance plan.

---

### Step 2: Setup a Free Cloud MySQL Database
Render provides native PostgreSQL, but this PHP webmail uses MySQL / MariaDB. You can get a free MySQL database in 1 minute from any of the following providers:
- **[Aiven for MySQL](https://aiven.io/)** (Free tier with 5 GB storage)
- **[Railway.app](https://railway.app/)** (One-click MySQL plugin with free tier credits)
- **[Clever Cloud](https://www.clever-cloud.com/)** (Free MySQL database)
- **[TiDB Cloud Serverless](https://tidbcloud.com/)** (Free MySQL-compatible cloud database)

---

### Step 3: Configure Environment Variables in Render Dashboard
In your Render Web Service settings, go to the **Environment** tab and add the following variables:

| Key | Example Value | Description |
|---|---|---|
| `APP_ENV` | `production` | Production environment mode |
| `APP_DEBUG` | `false` | Disable error stack traces in production |
| `APP_URL` | `https://your-app-name.onrender.com` | Your live Render URL |
| `DB_HOST` | `mysql-xxxx.aivencloud.com` | Remote MySQL server hostname |
| `DB_PORT` | `3306` (or provided port) | Remote MySQL port |
| `DB_DATABASE` | `defaultdb` (or `nkinfyx_email`) | Remote database name |
| `DB_USERNAME` | `avnadmin` | MySQL username |
| `DB_PASSWORD` | `your_remote_password` | MySQL user password |
| *or* `DATABASE_URL` | `mysql://user:pass@host:port/dbname` | Full MySQL connection URL string |
| `APP_KEY` | *(Click 'Generate')* | Random encryption secret key |
| `SESSION_LIFETIME` | `604800` | Session duration (7 days) |

#### Optional SMTP Relay (for sending real emails)
| Key | Example Value |
|---|---|
| `SMTP_ENABLED` | `true` |
| `SMTP_HOST` | `smtp.gmail.com` |
| `SMTP_PORT` | `587` |
| `SMTP_ENCRYPTION` | `tls` |
| `SMTP_USERNAME` | `your-email@gmail.com` |
| `SMTP_PASSWORD` | `your-google-app-password` |
| `SMTP_FROM_ADDRESS` | `your-email@gmail.com` |
| `SMTP_FROM_NAME` | `Novalyx Cloud Mail` |

---

### Step 4: Deploy & Automated Database Initialization
1. Click **Create Web Service** or **Deploy**.
2. Render will build the Docker container and start the service.
3. During container startup, `docker-entrypoint.sh` automatically runs `database/run_all_migrations.php`:
   - Automatically provisions all tables (`users`, `messages`, `labels`, `attachments`, `calendar_events`, `user_settings`, `blocked_senders`).
   - Seeds the default admin account:
     - **Email:** `adminnkinflyx@gmail.com`
     - **Password:** `password123`
4. Once deployed, open your Render web address:
   ```
   https://your-app-name.onrender.com/login
   ```
5. Sign in and enjoy your full Novalyx Cloud Webmail with responsive design, Settings area, Calendar, and attachments!
