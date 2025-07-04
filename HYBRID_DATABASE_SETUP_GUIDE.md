# 🚀 **HYBRID DATABASE SETUP GUIDE**
## Evidence Management System - MongoDB Atlas + MySQL/XAMPP

**Complete setup guide for the hybrid database architecture**

---

## 📋 **PREREQUISITES**

### **1. MongoDB Atlas Account**
- ✅ MongoDB Atlas cluster "Cine" is already created
- ✅ Connection string provided: `mongodb+srv://AndresGonzalez:<db_password>@cine.pmryl.mongodb.net/evidence_management?retryWrites=true&w=majority&appName=Cine`
- ⚠️ **REQUIRED:** Replace `<db_password>` with actual password

### **2. XAMPP Installation**
- Download and install XAMPP from: https://www.apachefriends.org/
- Ensure MySQL service can be started
- Access to phpMyAdmin (http://localhost/phpmyadmin)

---

## 🔧 **STEP-BY-STEP SETUP**

### **STEP 1: Configure MongoDB Atlas Password**

1. **Open the backend/.env file**
2. **Find this line:**
   ```env
   MONGODB_URI=mongodb+srv://AndresGonzalez:<db_password>@cine.pmryl.mongodb.net/evidence_management?retryWrites=true&w=majority&appName=Cine
   ```
3. **Replace `<db_password>` with your actual MongoDB Atlas password**
4. **Save the file**

**Example:**
```env
# Before (with placeholder)
MONGODB_URI=mongodb+srv://AndresGonzalez:<db_password>@cine.pmryl.mongodb.net/evidence_management?retryWrites=true&w=majority&appName=Cine

# After (with real password)
MONGODB_URI=mongodb+srv://AndresGonzalez:MyRealPassword123@cine.pmryl.mongodb.net/evidence_management?retryWrites=true&w=majority&appName=Cine
```

### **STEP 2: Start XAMPP and MySQL**

1. **Open XAMPP Control Panel**
2. **Start Apache** (optional, for phpMyAdmin)
3. **Start MySQL** (required)
4. **Verify MySQL is running** (green status in XAMPP)

### **STEP 3: Run the Automated Setup Script**

Open terminal in the project root and run:

```bash
cd backend
node database/setup-hybrid-database.js
```

**This script will automatically:**
- ✅ Verify MongoDB Atlas configuration
- ✅ Test MongoDB Atlas connection
- ✅ Check XAMPP/MySQL status
- ✅ Create MySQL database and tables
- ✅ Import MySQL schema and sample data
- ✅ Initialize MongoDB with comprehensive sample data
- ✅ Test hybrid database functionality
- ✅ Display setup summary

### **STEP 4: Manual MySQL Setup (Alternative)**

If the automated script fails, you can set up MySQL manually:

1. **Open phpMyAdmin** (http://localhost/phpmyadmin)
2. **Create new database:**
   - Database name: `evidence_management_mysql`
   - Collation: `utf8mb4_unicode_ci`
3. **Import schema:**
   - Go to Import tab
   - Choose file: `backend/database/mysql_schema.sql`
   - Click "Go" to import

### **STEP 5: Verify Setup**

Run the verification script:

```bash
cd backend
node -e "
const { getDatabaseStatus } = require('./config/database');
const { initializeDatabases } = require('./config/database');
initializeDatabases().then(() => {
  const status = getDatabaseStatus();
  console.log('Database Status:', JSON.stringify(status, null, 2));
});
"
```

---

## 🔍 **TESTING THE SETUP**

### **Test 1: Start the Backend Server**

```bash
cd backend
npm start
```

**Expected output:**
```
🚀 Inicializando bases de datos...
🍃 Intentando conectar a MongoDB Atlas...
✅ MongoDB conectado exitosamente (Atlas)
📊 Database: evidence_management
🌐 Host: cine-shard-00-02.pmryl.mongodb.net
✅ MySQL/XAMPP conectado exitosamente
🔄 Modelos MySQL sincronizados
✅ Ambas bases de datos conectadas exitosamente
🚀 Servidor iniciado en puerto 5002
```

### **Test 2: Health Check**

Open browser or use curl:
```bash
curl http://localhost:5002/health
```

**Expected response:**
```json
{
  "status": "OK",
  "timestamp": "2025-07-04T...",
  "uptime": 23.4,
  "environment": "development",
  "version": "v1",
  "databases": {
    "mongodb": {
      "connected": true,
      "type": "MongoDB Atlas"
    },
    "mysql": {
      "connected": true,
      "type": "MySQL/XAMPP"
    }
  }
}
```

### **Test 3: Authentication**

Test login with sample users:
```bash
curl -X POST http://localhost:5002/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"admin123"}'
```

**Expected response:**
```json
{
  "message": "Inicio de sesión exitoso",
  "user": {
    "id": "...",
    "email": "admin@test.com",
    "firstName": "Admin",
    "lastName": "User",
    "role": "admin"
  },
  "tokens": {
    "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

---

## 📊 **SAMPLE DATA OVERVIEW**

### **MongoDB Collections (Primary Data)**

| Collection | Count | Description |
|------------|-------|-------------|
| **users** | 12+ | Admin, analysts, investigators, regular users |
| **groups** | 9 | Research teams, departments, project groups |
| **files** | 12 | Documents, images, videos, various file types |
| **evidences** | 11 | All workflow states (pending, approved, rejected) |
| **messages** | 15 | Group and direct messages with attachments |
| **notifications** | 15 | All notification types and categories |

### **MySQL Tables (Analytics & Audit)**

| Table | Count | Description |
|-------|-------|-------------|
| **audit_logs** | 15+ | User actions, logins, file operations |
| **analytics** | 20+ | System metrics, user statistics, trends |
| **system_logs** | 15+ | Server logs, errors, warnings, debug info |
| **performance_metrics** | 15+ | API response times, resource usage |
| **user_sessions** | 7+ | Active and expired user sessions |
| **file_analytics** | 5+ | File upload/download statistics |
| **notification_analytics** | 4+ | Notification delivery and read rates |
| **group_analytics** | 5+ | Group activity and member engagement |
| **evidence_analytics** | 5+ | Evidence workflow and review metrics |

---

## 🎯 **TEST CREDENTIALS**

### **User Accounts (MongoDB)**

| Email | Password | Role | Department |
|-------|----------|------|------------|
| admin@test.com | admin123 | admin | IT |
| user@test.com | user123 | user | General |
| analyst@test.com | analyst123 | analyst | Analytics |
| investigator@test.com | investigator123 | investigator | Investigation |
| sofia.lopez@company.com | sofia123 | analyst | Research |
| miguel.torres@company.com | miguel123 | analyst | IT |
| laura.fernandez@company.com | laura123 | investigator | Legal |
| david.sanchez@company.com | david123 | investigator | Investigation |
| maria.garcia@company.com | maria123 | user | HR |
| carlos.rodriguez@company.com | carlos123 | user | Operations |

---

## 🚨 **TROUBLESHOOTING**

### **MongoDB Atlas Issues**

**Problem:** Authentication failed
```
❌ Error conectando a MongoDB Atlas: Authentication failed
```
**Solution:**
1. Verify password in .env file
2. Check MongoDB Atlas user permissions
3. Ensure IP address is whitelisted in Atlas

**Problem:** Network timeout
```
❌ Error conectando a MongoDB Atlas: Server selection timed out
```
**Solution:**
1. Check internet connection
2. Verify MongoDB Atlas cluster is running
3. Check firewall settings

### **MySQL/XAMPP Issues**

**Problem:** Connection refused
```
❌ Error conectando a MySQL/XAMPP: connect ECONNREFUSED 127.0.0.1:3306
```
**Solution:**
1. Start XAMPP Control Panel
2. Start MySQL service
3. Check if port 3306 is available

**Problem:** Access denied
```
❌ Error conectando a MySQL/XAMPP: Access denied for user 'root'@'localhost'
```
**Solution:**
1. Check MySQL credentials in .env file
2. Reset MySQL root password in XAMPP
3. Verify MySQL user permissions

### **Hybrid Mode Issues**

**Problem:** Only one database connected
```
⚠️ Solo MongoDB Atlas disponible
```
**Solution:**
- This is normal if one database is offline
- The system will work with reduced functionality
- Fix the offline database to restore full functionality

---

## ✅ **SUCCESS INDICATORS**

### **Full Hybrid Mode (Best Case)**
- ✅ MongoDB Atlas connected
- ✅ MySQL/XAMPP connected
- ✅ All 14 frontend views functional
- ✅ Real-time analytics and audit logging
- ✅ Complete user management and workflows

### **MongoDB Only Mode**
- ✅ MongoDB Atlas connected
- ❌ MySQL/XAMPP offline
- ✅ Core functionality works (users, files, groups, evidences)
- ❌ Limited analytics and audit logging
- ⚠️ Some admin features may be limited

### **MySQL Only Mode**
- ❌ MongoDB Atlas offline
- ✅ MySQL/XAMPP connected
- ⚠️ Fallback development users only
- ✅ Analytics and audit logging functional
- ⚠️ Limited user management features

### **Fallback Development Mode**
- ❌ Both databases offline
- ⚠️ Hardcoded development users
- ⚠️ No data persistence
- ✅ Frontend development and testing possible
- ❌ No production functionality

---

## 🚀 **NEXT STEPS AFTER SETUP**

1. **Start Frontend:**
   ```bash
   npm run dev
   ```

2. **Access Application:**
   - Frontend: http://localhost:3000
   - Backend: http://localhost:5002
   - Health Check: http://localhost:5002/health

3. **Test All Views:**
   - Login with admin@test.com/admin123
   - Navigate through all 14 frontend views
   - Test file uploads, evidence submissions, group messaging

4. **Monitor System:**
   - Check backend logs for any errors
   - Verify database connections remain stable
   - Test user workflows end-to-end

**🎉 Your hybrid database system is now ready for development and testing!**
