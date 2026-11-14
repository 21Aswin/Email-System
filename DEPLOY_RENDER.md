# Render.com Deployment Guide - Novalyx Cloud Webmail

This repository is **100% plug-and-play ready** for instant deployment on **[Render](https://render.com/)**.

---

## ⚡ Option 1: 1-Click Instant Deployment (Zero Setup Needed!)

You don't need to configure an external database unless you want to! The container includes an internal MariaDB database engine pre-configured for automated startup.

### Steps:
1. Open [dashboard.render.com](https://dashboard.render.com/).
2. Click **New +** and select **Web Service**.
3. Connect your GitHub repository:
   ```
   https://github.com/21Aswin/Email-System.git
   ```
4. Render will automatically detect the **Dockerfile** in the repository root.
5. Select the **Free** instance plan.
6. Click **Create Web Service**!
7. Render will build and deploy the application.
8. Once deployment finishes, open your live URL (e.g., `https://your-app-name.onrender.com/login`).

---

## 🔑 Default Sign In Credentials

Once deployed on Render, sign in with the pre-seeded administrator credentials:
- **Email:** `adminnkinflyx@gmail.com`
- **Password:** `password123`

---

## 🌐 Option 2: Using an External Cloud MySQL Database (Optional)

If you prefer to connect an external managed MySQL database (like Aiven, Railway, TiDB, or PlanetScale), simply go to the **Environment** tab in your Render Web Service settings and add:

- `DB_HOST`: `your-remote-db-host.com`
- `DB_PORT`: `3306`
- `DB_DATABASE`: `your_database_name`
- `DB_USERNAME`: `your_database_username`
- `DB_PASSWORD`: `your_database_password`
*(Or `DATABASE_URL` = `mysql://user:pass@host:port/dbname`)*

The container will automatically detect the external `DB_HOST` and connect to it instead of the local engine!

---

## 📧 Outbound Real SMTP Configuration

Real SMTP sending is already pre-configured in `render.yaml` with Google SMTP credentials:
- **SMTP Server:** `smtp.gmail.com:587`
- **Account:** `novalyx.workspace@gmail.com`
- **Encryption:** `TLS`

Emails sent from the Compose window will be delivered to real recipient inboxes worldwide!
