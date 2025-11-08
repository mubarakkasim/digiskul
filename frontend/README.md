# DIGISKUL Frontend - README

## Setup Instructions

1. Install dependencies:
```bash
cd frontend
npm install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Update `.env` with your API base URL

4. Start development server:
```bash
npm run dev
```

5. Build for production:
```bash
npm run build
```

## Project Structure

- `/src` - Source files
  - `/components` - Reusable Vue components
  - `/views` - Page components
  - `/stores` - Pinia stores (auth, sync)
  - `/router` - Vue Router configuration
  - `/layouts` - Layout components
  - `/utils` - Utility functions

## Features

- ✅ Vue.js 3 with Composition API
- ✅ PWA with Service Worker
- ✅ Offline-first with IndexedDB (Dexie.js)
- ✅ Background Sync API
- ✅ TailwindCSS for styling
- ✅ Vue Router for navigation
- ✅ Pinia for state management
- ✅ Axios for API calls
- ✅ Toast notifications

## Offline Capabilities

The app stores data locally using IndexedDB when offline and syncs automatically when connection is restored.

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)

