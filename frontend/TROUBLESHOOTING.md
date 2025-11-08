# Troubleshooting Blank Screen

## Common Causes & Solutions

### 1. Check Browser Console
Open browser DevTools (F12) and check the Console tab for errors.

### 2. Check Network Tab
Verify that all files are loading correctly (no 404 errors).

### 3. Clear Browser Cache
- Press Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac) to hard refresh
- Or clear browser cache completely

### 4. Check if Backend is Running
Make sure the Laravel backend is running on `http://localhost:8000`

### 5. Verify Environment Variables
Check `frontend/.env` file exists and has:
```
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

### 6. Reinstall Dependencies
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### 7. Check for JavaScript Errors
Look for these common issues:
- Missing imports
- Undefined variables
- Module resolution errors

### 8. Verify Vue App is Mounting
Add this to `main.js` temporarily to check:
```javascript
console.log('App mounting...')
app.mount('#app')
console.log('App mounted!')
```

## Quick Fix Steps

1. **Stop the dev server** (Ctrl+C)
2. **Clear node_modules and reinstall:**
   ```bash
   cd frontend
   rm -rf node_modules
   npm install
   ```
3. **Restart dev server:**
   ```bash
   npm run dev
   ```
4. **Hard refresh browser** (Ctrl+Shift+R)

## Expected Behavior

- On first visit: Should show login page
- If logged in: Should redirect to dashboard
- If errors: Check browser console for details

