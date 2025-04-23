// index.tsx

import { store } from '@/stores/store'
import { useRootNavigationState, Redirect } from 'expo-router'
import * as Notifications from 'expo-notifications'
import Constants from 'expo-constants'
import { useEffect, useRef, useState } from 'react'
import { Platform } from 'react-native'
import * as Device from 'expo-device'
import { addToken, deleteToken } from '@/api/api'
import AsyncStorage from '@react-native-async-storage/async-storage'

Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: false,
    shouldSetBadge: false
  })
})

console.warn = () => {}

export default function App() {
  const [channels, setChannels] = useState<Notifications.NotificationChannel[]>(
    []
  )
  const notificationListener = useRef<Notifications.Subscription>()
  const responseListener = useRef<Notifications.Subscription>()
  const rootNavigationState = useRootNavigationState()
  const user = store.getState()?.user?.userInfo

  async function fetchPushToken() {
    try {
      const token = await registerForPushNotificationsAsync()
      if (token) {
        AsyncStorage.setItem('expoPushToken', token)
        return true
      }
      return false
    } catch (error) {
      return false
    }
  }

  useEffect(() => {
    fetchPushToken()

    if (Platform.OS === 'android') {
      Notifications.getNotificationChannelsAsync().then((value) =>
        setChannels(value ?? [])
      )
    }

    notificationListener.current =
      Notifications.addNotificationReceivedListener((notification) => {
        console.log('Notification Received:', notification)
      })

    responseListener.current =
      Notifications.addNotificationResponseReceivedListener((response) => {
        console.log('Notification Response:', response)
      })

    return () => {
      if (notificationListener.current) {
        Notifications.removeNotificationSubscription(
          notificationListener.current
        )
      }
      if (responseListener.current) {
        Notifications.removeNotificationSubscription(responseListener.current)
      }
    }
  }, [])

  // Store token and call API whenever `user` changes
  useEffect(() => {
    async function handleUserChange() {
      const expoPushToken = await AsyncStorage.getItem('expoPushToken')

      if (expoPushToken) {
        // Store token in AsyncStorage
        await AsyncStorage.setItem('expoPushToken', expoPushToken)
        console.log('Token stored in AsyncStorage:', expoPushToken)

        // Call addToken API
        try {
          await addToken({ username: user?.username, token: expoPushToken })
          console.log('Token added successfully for user:', user.username)
        } catch (error) {
          console.error(
            'Error adding token:',
            error?.response?.data?.messages?.[0]
          )
        }
      } else {
        const granted = await fetchPushToken()
        if (granted) {
          await handleUserChange()
        }
      }
    }

    if (user) {
      handleUserChange()
    }
  }, [user])

  if (!rootNavigationState?.key) return null

  if (user && user?.email) return <Redirect href="/(tabs)/home" />

  return <Redirect href="/login" />
}

export async function registerForPushNotificationsAsync(): Promise<
  string | null
> {
  if (!Device.isDevice) {
    // alert('Must use physical device for Push Notifications')
    return null
  }

  const { status: existingStatus } = await Notifications.getPermissionsAsync()
  let finalStatus = existingStatus

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync()
    finalStatus = status
  }

  if (finalStatus !== 'granted') {
    console.log('Failed to get push token for push notification!')
    return null
  }

  try {
    const projectId =
      Constants?.expoConfig?.extra?.eas?.projectId ||
      Constants?.easConfig?.projectId
    if (!projectId) throw new Error('Project ID not found')

    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'default',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#002F6C'
      })
    }

    const token = (await Notifications.getExpoPushTokenAsync({ projectId }))
      .data
    console.log('Expo Push Token:', token)
    return token
  } catch (error) {
    console.error('Error fetching Expo Push Token:', error)
    return null
  }
}
