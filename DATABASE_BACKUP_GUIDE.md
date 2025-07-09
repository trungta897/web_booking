# 🗄️ Database Backup Strategy Guide

## 📋 **Overview**

This guide covers the comprehensive database backup strategy implemented for the Web Booking platform, including automated backups, manual operations, restoration procedures, and monitoring.

---

## 🎯 **Backup Strategy**

### **Backup Types**

1. **Full Backup** (Structure + Data)
   - Complete database dump
   - Includes all tables, data, triggers, procedures
   - Used for: Complete disaster recovery

2. **Structure Backup** (Schema Only)
   - Database schema without data
   - Used for: Development setup, migrations

3. **Data Backup** (Data Only)
   - All data without schema
   - Used for: Data migration, testing

### **Backup Schedule**

| Type | Frequency | Time | Retention | Cloud Upload |
|------|-----------|------|-----------|--------------|
| Daily | Every day | 2:00 AM | 30 days | ❌ |
| Weekly | Sunday | 3:00 AM | 12 weeks | ✅ |
| Monthly | 1st of month | 4:00 AM | 12 months | ✅ |

---

## 🛠️ **Commands & Usage**

### **Manual Backup Commands**

```bash
# Basic full backup
php artisan db:backup

# Full backup with compression
php artisan db:backup --compress

# Structure-only backup
php artisan db:backup --type=structure

# Data-only backup  
php artisan db:backup --type=data

# Backup with cloud upload
php artisan db:backup --upload

# Backup with cleanup of old files
php artisan db:backup --cleanup

# Custom named backup
php artisan db:backup --name=pre-migration-backup

# Combined options
php artisan db:backup --type=full --compress --upload --cleanup --name=weekly_backup
```

### **Restoration Commands**

```bash
# List available backups
php artisan db:restore --list

# Restore latest backup
php artisan db:restore --latest

# Restore specific backup file
php artisan db:restore backup_file.sql

# Force restore without confirmation
php artisan db:restore backup_file.sql --force

# Interactive restore (select from list)
php artisan db:restore
```

### **Automation Scripts**

```bash
# Run daily backup
./scripts/backup-automation.sh daily

# Run weekly backup
./scripts/backup-automation.sh weekly

# Run monthly backup
./scripts/backup-automation.sh monthly

# Interactive custom backup
./scripts/backup-automation.sh custom

# Setup automated scheduling
./scripts/backup-automation.sh setup-cron

# Remove automation
./scripts/backup-automation.sh remove-cron

# Check status
./scripts/backup-automation.sh status
```

---

## 📁 **File Organization**

### **Backup Locations**

```
storage/app/backups/
├── web_booking_full_backup_2025-01-09_14-30-00.sql
├── web_booking_full_backup_2025-01-09_14-30-00.sql.gz
├── weekly_backup_2025-01-09_03-00-00.sql.gz
├── monthly_backup_2025-01-01_04-00-00.sql.gz
└── custom_backup_2025-01-09_16-45-30.sql
```

### **Cloud Storage Structure**

```
s3://your-bucket/database-backups/
├── 2025/
│   ├── 01/
│   │   ├── weekly_backup_2025-01-05_03-00-00.sql.gz
│   │   ├── weekly_backup_2025-01-12_03-00-00.sql.gz
│   │   └── monthly_backup_2025-01-01_04-00-00.sql.gz
│   └── 02/
│       └── monthly_backup_2025-02-01_04-00-00.sql.gz
```

### **Log Files**

```
storage/logs/
├── backup-automation.log      # Automation script logs
├── laravel.log                # General application logs
├── payment.log                # Payment-specific logs
├── booking.log                # Booking-specific logs
└── error.log                  # Error-only logs
```

---

## ⚙️ **Configuration**

### **Environment Variables**

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_booking
DB_USERNAME=root
DB_PASSWORD=your_password

# Cloud Storage (Optional)
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-backup-bucket

# Backup Settings
BACKUP_RETENTION_DAYS=30
BACKUP_COMPRESSION=true
BACKUP_CLOUD_UPLOAD=false
```

### **Logging Configuration**

The backup system uses Laravel's multi-channel logging:

```php
// config/logging.php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,
    ],
    'payment' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payment.log'),
        'level' => 'debug',
        'days' => 30,
    ],
    // ... other channels
]
```

---

## 🔧 **Installation & Setup**

### **1. Prerequisites**

```bash
# Install MySQL client tools
sudo apt-get install mysql-client

# Or on CentOS/RHEL
sudo yum install mysql

# Or on macOS
brew install mysql-client
```

### **2. Setup Commands**

```bash
# Register commands (done automatically)
php artisan list | grep db:

# Create backup directory
mkdir -p storage/app/backups

# Set permissions
chmod 755 storage/app/backups

# Test backup command
php artisan db:backup --type=structure
```

### **3. Setup Automation**

```bash
# Make script executable
chmod +x scripts/backup-automation.sh

# Setup cron jobs
./scripts/backup-automation.sh setup-cron

# Verify cron installation
crontab -l | grep backup
```

---

## 🚨 **Disaster Recovery Procedures**

### **Complete Database Loss**

1. **Assess the situation**
   ```bash
   # Check database connectivity
   php artisan db:restore --list
   ```

2. **Select appropriate backup**
   ```bash
   # List available backups by date
   ls -la storage/app/backups/
   ```

3. **Restore database**
   ```bash
   # Restore latest backup
   php artisan db:restore --latest
   
   # Or restore specific backup
   php artisan db:restore monthly_backup_2025-01-01_04-00-00.sql.gz
   ```

4. **Verify restoration**
   ```bash
   # Check application functionality
   php artisan migrate:status
   php artisan queue:work --once
   ```

### **Partial Data Loss**

1. **Create current backup first**
   ```bash
   php artisan db:backup --name=before-partial-restore
   ```

2. **Identify affected tables**
   ```sql
   SHOW TABLES;
   SELECT COUNT(*) FROM affected_table;
   ```

3. **Selective restoration**
   - Extract specific tables from backup
   - Use manual SQL commands for targeted restore

### **Corruption Recovery**

1. **Stop application**
   ```bash
   php artisan down
   ```

2. **Backup current state**
   ```bash
   php artisan db:backup --name=corrupted-state
   ```

3. **Restore from known good backup**
   ```bash
   php artisan db:restore --latest
   ```

4. **Verify and bring online**
   ```bash
   php artisan up
   ```

---

## 📊 **Monitoring & Alerts**

### **Backup Health Checks**

```bash
# Check backup status
./scripts/backup-automation.sh status

# View recent logs
tail -f storage/logs/backup-automation.log

# Check backup file sizes
du -h storage/app/backups/

# Verify backup integrity
gunzip -t backup_file.sql.gz
```

### **Automated Monitoring**

The system logs all backup operations with structured data:

```php
// Successful backup log
LogService::database('Database backup created successfully', [
    'type' => 'full',
    'filename' => 'backup.sql.gz',
    'size_bytes' => 1048576,
    'duration_seconds' => 15.3,
    'compressed' => true,
    'uploaded' => true,
]);

// Failed backup log
LogService::error('Database backup failed', $exception, [
    'backup_type' => 'full',
    'duration' => 5.2,
]);
```

### **Alert Integration**

Set up alerts for backup failures:

```bash
# Email alerts on backup failure
# Add to cron job:
0 2 * * * /path/to/backup-script.sh daily || echo "Backup failed" | mail -s "Backup Alert" admin@example.com
```

---

## 🔒 **Security Best Practices**

### **Access Control**

1. **File Permissions**
   ```bash
   chmod 600 storage/app/backups/*.sql*
   chown www-data:www-data storage/app/backups/
   ```

2. **Directory Protection**
   ```apache
   # .htaccess in storage/app/backups/
   Deny from all
   ```

3. **Encryption** (Recommended for sensitive data)
   ```bash
   # Encrypt backup before cloud upload
   gpg --cipher-algo AES256 --compress-algo 1 --s2k-mode 3 \
       --s2k-digest-algo SHA512 --s2k-count 65536 --force-mdc \
       --symmetric backup.sql
   ```

### **Cloud Storage Security**

1. **IAM Policies** (AWS S3)
   ```json
   {
     "Version": "2012-10-17",
     "Statement": [
       {
         "Effect": "Allow",
         "Action": [
           "s3:PutObject",
           "s3:GetObject"
         ],
         "Resource": "arn:aws:s3:::your-backup-bucket/database-backups/*"
       }
     ]
   }
   ```

2. **Bucket Policies**
   - Enable versioning
   - Set lifecycle policies
   - Enable server-side encryption

---

## 🧪 **Testing & Validation**

### **Regular Testing Schedule**

| Test Type | Frequency | Description |
|-----------|-----------|-------------|
| Backup Creation | Daily | Verify backup files are created |
| Restoration Test | Weekly | Test restore on development environment |
| Full DR Test | Monthly | Complete disaster recovery simulation |

### **Test Procedures**

1. **Backup Creation Test**
   ```bash
   # Create test backup
   php artisan db:backup --type=structure --name=test-backup
   
   # Verify file exists and has content
   ls -la storage/app/backups/test-backup*
   head -n 20 storage/app/backups/test-backup*.sql
   ```

2. **Restoration Test**
   ```bash
   # Create test database
   mysql -e "CREATE DATABASE test_restore;"
   
   # Restore to test database
   mysql test_restore < storage/app/backups/latest-backup.sql
   
   # Verify data integrity
   mysql test_restore -e "SHOW TABLES; SELECT COUNT(*) FROM bookings;"
   
   # Cleanup
   mysql -e "DROP DATABASE test_restore;"
   ```

---

## 📈 **Performance Optimization**

### **Backup Performance**

1. **Use compression** to reduce file size and transfer time
2. **Schedule during low-traffic hours** (2-4 AM)
3. **Use incremental backups** for large databases (future enhancement)
4. **Parallel processing** for multiple databases

### **Storage Optimization**

1. **Retention policies** - automatic cleanup of old backups
2. **Cloud storage tiers** - move old backups to cheaper storage
3. **Deduplication** - identify and remove duplicate backups

### **Network Optimization**

1. **Bandwidth throttling** during business hours
2. **Resume capability** for large cloud uploads
3. **Multi-part uploads** for better reliability

---

## 🐛 **Troubleshooting**

### **Common Issues**

1. **"mysqldump: command not found"**
   ```bash
   # Install MySQL client
   sudo apt-get install mysql-client
   # Or add to PATH
   export PATH=$PATH:/usr/local/mysql/bin
   ```

2. **"Permission denied" errors**
   ```bash
   # Fix directory permissions
   chmod 755 storage/app/backups
   chmod 644 storage/app/backups/*.sql*
   ```

3. **"Disk space full" errors**
   ```bash
   # Check available space
   df -h
   
   # Cleanup old backups
   php artisan db:backup --cleanup
   ```

4. **Cloud upload failures**
   ```bash
   # Check AWS credentials
   aws s3 ls
   
   # Test upload manually
   aws s3 cp backup.sql s3://your-bucket/test/
   ```

### **Log Analysis**

```bash
# Check backup logs
grep "backup" storage/logs/laravel.log | tail -20

# Check error logs
grep "ERROR" storage/logs/backup-automation.log

# Monitor real-time
tail -f storage/logs/laravel.log | grep -i backup
```

---

## 📚 **Additional Resources**

### **Related Documentation**

- [MySQL Backup Best Practices](https://dev.mysql.com/doc/refman/8.0/en/backup-methods.html)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
- [AWS S3 Storage Classes](https://aws.amazon.com/s3/storage-classes/)

### **Useful Commands Reference**

```bash
# Quick backup
php artisan db:backup

# Quick restore
php artisan db:restore --latest

# Check status
./scripts/backup-automation.sh status

# Setup automation
./scripts/backup-automation.sh setup-cron

# Emergency recovery
php artisan db:restore --force backup_file.sql
```

---

## 💡 **Future Enhancements**

1. **Incremental Backups** - Only backup changed data
2. **Point-in-Time Recovery** - Binary log-based recovery
3. **Cross-Region Replication** - Geographic backup distribution
4. **Database Health Monitoring** - Proactive issue detection
5. **Automated Testing** - Continuous backup validation
6. **Encryption at Rest** - Enhanced security for sensitive data

---

**📞 Support**: For issues or questions, check the logs first, then consult this guide. For critical failures, follow the disaster recovery procedures immediately. 
