import { defineStore } from 'pinia'
import { ref } from 'vue'
import Dexie from 'dexie'
import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

// Initialize IndexedDB
const db = new Dexie('DigiskulDB')
db.version(1).stores({
  syncQueue: '++id, type, table, payload, local_ts, device_id, synced, server_ts, server_id',
  attendance: '++id, school_id, class_id, student_id, date, session, status, recorded_by, synced_at, device_tx_id',
  grades: '++id, school_id, student_id, subject_id, term, session, score, recorded_by, comment, synced_at, device_tx_id',
  payments: '++id, school_id, student_id, amount, method, trx_id, payment_date, recorded_by, receipt_no, synced_at, device_tx_id',
  students: '++id, school_id, full_name, admission_no, class_id, synced_at',
  classes: '++id, school_id, name, section, level, synced_at'
})

export const useSyncStore = defineStore('sync', () => {
  const syncing = ref(false)
  const lastSyncTime = ref(null)
  const deviceId = ref(localStorage.getItem('device_id') || generateDeviceId())

  // Generate unique device ID
  function generateDeviceId() {
    const id = `device_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
    localStorage.setItem('device_id', id)
    return id
  }

  // Add operation to sync queue
  const addToSyncQueue = async (type, table, payload) => {
    const operation = {
      type, // INSERT, UPDATE, DELETE
      table,
      payload,
      local_ts: new Date().toISOString(),
      device_id: deviceId.value,
      synced: false
    }
    
    await db.syncQueue.add(operation)
    
    // Try to sync immediately if online
    if (navigator.onLine) {
      await sync()
    }
    
    return operation
  }

  // Sync operations to server
  const sync = async () => {
    if (syncing.value || !navigator.onLine) return
    
    syncing.value = true
    
    try {
      const pendingOps = await db.syncQueue.where('synced').equals(0).toArray()
      
      if (pendingOps.length === 0) {
        lastSyncTime.value = new Date().toISOString()
        return { success: true, synced: 0 }
      }

      const response = await axios.post(`${API_BASE_URL}/sync/bulk`, {
        device_id: deviceId.value,
        operations: pendingOps
      })

      const { synced_operations } = response.data.data

      // Update synced operations
      for (const syncedOp of synced_operations) {
        await db.syncQueue.update(syncedOp.device_tx_id, {
          synced: true,
          server_ts: syncedOp.server_ts,
          server_id: syncedOp.server_id
        })
      }

      lastSyncTime.value = new Date().toISOString()
      
      return { success: true, synced: synced_operations.length }
    } catch (error) {
      console.error('Sync error:', error)
      throw error
    } finally {
      syncing.value = false
    }
  }

  // Register background sync
  const registerBackgroundSync = () => {
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
      navigator.serviceWorker.ready.then((registration) => {
        registration.sync.register('sync-operations').catch((err) => {
          console.error('Background sync registration failed:', err)
        })
      })
    }
  }

  // Check sync status
  const getSyncStatus = async () => {
    const pendingCount = await db.syncQueue.where('synced').equals(0).count()
    return {
      pending: pendingCount,
      lastSync: lastSyncTime.value,
      isOnline: navigator.onLine
    }
  }

  return {
    syncing,
    lastSyncTime,
    deviceId,
    addToSyncQueue,
    sync,
    registerBackgroundSync,
    getSyncStatus,
    db
  }
})

