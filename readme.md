
# Drupal Project with Docker, DDEV & Composer

This project is a Drupal site managed with **DDEV**, **Docker**, and **Composer**. Follow these steps to set it up locally.

---

## 🚀 Prerequisites

Ensure you have the following installed:

- [Docker](https://docs.docker.com/get-docker/)
- [DDEV](https://ddev.readthedocs.io/en/stable/)
- [Git](https://git-scm.com/)
- [Composer](https://getcomposer.org/)

---

## 📥 Clone the Project

```sh
git clone https://github.com/sapatevaibhav/Drupal-College-Site
cd Drupal-College-Site
```

---

## ⚙️ Start the Local Environment with DDEV

1. **Initialize DDEV** (only needed the first time):
   ```sh
   ddev config --project-type=drupal --docroot=web --create-docroot
   ```

2. **Start DDEV**:
   ```sh
   ddev start
   ```

3. **Install dependencies**:
   ```sh
  ddev composer install
   ```
If you face issue here run below Commands

```bash
    sudo apt update
    sudo apt install php-gd
    sudo apt install php-xml
```


---

## 🏗️ Install Drupal

If setting up a new site, run:

```sh
ddev drush site:install --account-name=admin --account-pass=admin
```

If using an existing database, import it:

```sh
ddev import-db --src=your_database.sql.gz
```

---

## 🔗 Access Your Site

- **Frontend**: [http://YOUR_PROJECT_NAME.ddev.site](http://YOUR_PROJECT_NAME.ddev.site)
- **DDEV Admin**: `ddev describe`
- **Drupal Admin**: [http://YOUR_PROJECT_NAME.ddev.site/user/login](http://YOUR_PROJECT_NAME.ddev.site/user/login)
  - Username: `admin`
  - Password: `admin`

---

## 🎯 Common DDEV Commands

| Command | Description |
|---------|-------------|
| `ddev start` | Start the project |
| `ddev stop` | Stop the project |
| `ddev restart` | Restart DDEV |
| `ddev ssh` | Access the container |
| `ddev drush status` | Check Drupal status |
| `ddev import-db --src=db.sql.gz` | Import a database |
| `ddev export-db --file=db.sql.gz` | Export the database |

---

## 🛠️ Troubleshooting

- If DDEV is not working, try:
  ```sh
  ddev poweroff
  ddev start
  ```
- If Composer fails, clear cache:
  ```sh
  composer clear-cache
  ```

---
