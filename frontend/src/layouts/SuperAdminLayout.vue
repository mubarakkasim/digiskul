<template>
  <div class="super-admin-layout">
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" :class="{ 'active': mobileMenuOpen }" @click="mobileMenuOpen = false"></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'collapsed': sidebarCollapsed, 'mobile-open': mobileMenuOpen }">
      <div class="sidebar-header">
        <div class="logo">
          <span class="logo-icon">🎓</span>
          <span class="logo-text" v-if="!sidebarCollapsed">DIGISKUL</span>
        </div>
        <span class="admin-badge" v-if="!sidebarCollapsed">Super Admin</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/super-admin/dashboard" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
          <span class="nav-icon">📊</span>
          <span class="nav-text" v-if="!sidebarCollapsed">Dashboard</span>
        </router-link>

        <div class="nav-group">
          <div class="nav-group-title" v-if="!sidebarCollapsed">MANAGEMENT</div>
          <router-link to="/super-admin/schools" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">🏫</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Schools</span>
          </router-link>
          <router-link to="/super-admin/licenses" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">📜</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Licenses</span>
          </router-link>
          <router-link to="/super-admin/users" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">👥</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Users</span>
          </router-link>
        </div>

        <div class="nav-group">
          <div class="nav-group-title" v-if="!sidebarCollapsed">CONFIGURATION</div>
          <router-link to="/super-admin/settings" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Settings</span>
          </router-link>
          <router-link to="/super-admin/announcements" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">📢</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Announcements</span>
          </router-link>
        </div>

        <div class="nav-group">
          <div class="nav-group-title" v-if="!sidebarCollapsed">MONITORING</div>
          <router-link to="/super-admin/health" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">❤️</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Health</span>
          </router-link>
          <router-link to="/super-admin/logs" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">📋</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Logs</span>
          </router-link>
          <router-link to="/super-admin/backups" class="nav-item" active-class="active" @click="mobileMenuOpen = false">
            <span class="nav-icon">💾</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Backups</span>
          </router-link>
        </div>
      </nav>

      <button class="toggle-btn" @click="sidebarCollapsed = !sidebarCollapsed">
        {{ sidebarCollapsed ? '→' : '←' }}
      </button>
    </aside>

    <!-- Main Content wrapper -->
    <div class="main-wrapper">
      <!-- Header -->
      <header class="main-header">
        <div class="header-left">
          <span class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">☰</span>
          <h1 class="page-title">{{ pageTitle }}</h1>
        </div>

        <div class="header-right">
          <div class="header-item health">
            <div class="health-score" :class="healthScoreClass">
              Score: {{ healthScore }}%
            </div>
          </div>

          <div class="header-item user-menu" @click="showUserMenu = !showUserMenu">
            <div class="user-avatar">{{ userInitials }}</div>
            <span class="user-name" v-if="!isMobile">{{ userName }}</span>
            
            <div class="user-dropdown" v-if="showUserMenu" @click.stop>
              <router-link to="/super-admin/profile" class="dropdown-item">My Profile</router-link>
              <router-link to="/super-admin/settings" class="dropdown-item">Settings</router-link>
              <button @click="handleLogout" class="dropdown-item logout">Logout</button>
            </div>
          </div>
        </div>
      </header>

      <main class="main-content">
        <router-view></router-view>
      </main>

      <!-- Footer -->
      <footer class="main-footer">
        <div class="footer-left">
          &copy; {{ new Date().getFullYear() }} DIGISKUL Platform.
        </div>
        <div class="footer-right">
          {{ currentTime }}
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const sidebarCollapsed = ref(false)
const mobileMenuOpen = ref(false)
const showUserMenu = ref(false)
const healthScore = ref(100)
const currentTime = ref('')

const isMobile = computed(() => window.innerWidth <= 768)
const userName = computed(() => authStore.user?.full_name || 'Super Admin')
const userInitials = computed(() => {
  const name = userName.value
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const pageTitle = computed(() => route.meta.title || 'Super Admin')

const healthScoreClass = computed(() => {
  if (healthScore.value >= 90) return 'excellent'
  if (healthScore.value >= 70) return 'good'
  return 'warning'
})

const updateTime = () => {
  currentTime.value = new Date().toLocaleTimeString()
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

let timeInterval
onMounted(() => {
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  clearInterval(timeInterval)
})
</script>

<style scoped>
/* Color Palette from Image */
:root {
  --sa-primary-blue: #1A4FC4;
  --sa-accent-yellow: #FDBB2B;
  --sa-text-white: #FFFFFF;
  --sa-text-dark: #1E293B;
  --sa-bg-light: #F1F5F9;
  --sa-sidebar-width: 260px;
  --sa-sidebar-collapsed: 70px;
}

.super-admin-layout {
  display: flex;
  min-height: 100vh;
  background: #F1F5F9;
  font-family: 'Inter', sans-serif;
}

/* Sidebar Styling */
.sidebar {
  width: 260px;
  background: #1A4FC4;
  color: white;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
  position: fixed;
  height: 100vh;
  z-index: 1000;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar-header {
  padding: 1.5rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-icon {
  background: white;
  padding: 4px;
  border-radius: 6px;
  font-size: 1.2rem;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

.admin-badge {
  display: inline-block;
  margin-top: 5px;
  font-size: 0.65rem;
  padding: 2px 6px;
  background: #FDBB2B;
  color: #1E293B;
  font-weight: 800;
  border-radius: 4px;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0.75rem;
  overflow-y: auto;
}

.nav-group-title {
  font-size: 0.7rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.5);
  padding: 1rem 0.75rem 0.5rem;
  text-transform: uppercase;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 1rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  border-radius: 10px;
  margin-bottom: 3px;
  transition: all 0.2s;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.active {
  background: #FDBB2B;
  color: #1E293B;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(253, 187, 43, 0.3);
}

.toggle-btn {
  position: absolute;
  bottom: 2rem;
  right: -15px;
  width: 30px;
  height: 30px;
  background: #FDBB2B;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  z-index: 1001;
}

/* Main content area */
.main-wrapper {
  flex: 1;
  margin-left: 260px;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.sidebar.collapsed + .main-wrapper {
  margin-left: 70px;
}

.main-header {
  height: 70px;
  background: white;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  position: sticky;
  top: 0;
  z-index: 10;
}

.mobile-menu-btn {
  font-size: 1.5rem;
  cursor: pointer;
  color: #1E293B;
  display: none;
  margin-right: 1rem;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.health-score {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
}

.health-score.excellent { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.health-score.good { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.user-avatar {
  width: 38px;
  height: 38px;
  background: #1A4FC4;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  border: 2px solid #FDBB2B;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  width: 200px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  text-align: left;
  border: none;
  background: none;
  color: #1E293B;
  text-decoration: none;
  font-size: 0.9rem;
  cursor: pointer;
}

.dropdown-item:hover { background: #F8FAFC; }
.dropdown-item.logout { color: #EF4444; }

.main-content {
  padding: 2rem;
  flex: 1;
}

.main-footer {
  padding: 1rem 2rem;
  background: white;
  border-top: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #64748B;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 280px !important;
  }
  
  .sidebar.mobile-open {
    transform: translateX(0);
  }

  .main-wrapper {
    margin-left: 0 !important;
  }

  .sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
  }

  .sidebar-overlay.active {
    display: block;
  }
  
  .mobile-menu-btn {
    display: block;
  }
}
</style>
