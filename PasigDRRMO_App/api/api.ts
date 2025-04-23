import axios, { AxiosRequestConfig } from 'axios'
import {
  Announcement,
  AccountInfo,
  Contact,
  OngoingEvent,
  LoginPayload,
  RegisterPayload,
  LoginResponse,
  MobileLocatePayload,
  MobileRespondPayload,
  MobileRespondResponse,
  MobileRespond
} from '@/api/api.types'
import { format } from 'date-fns'

const API_URL = 'https://pasigdrrmo.site/pasig-api/v1/account'

const apiMultiPartRequest = async <T>(
  payload: Record<string, any> = {}
): Promise<T> => {
  try {
    // Axios request configuration
    const config: AxiosRequestConfig = {
      method: 'post',
      maxBodyLength: Infinity,
      url: API_URL,
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      data: payload // Adding the payload to the request
    }

    console.log('Request config:', config) // Log the configuration for debugging

    // Sending the request
    const response = await axios.request(config)

    return response.data // Return the data from the response
  } catch (error: any) {
    console.error('API request failed:', error?.response || error.message)
    throw error
  }
}

// Helper function to make POST requests to the API
const apiRequest = async <T>(mode: string, payload: any = {}): Promise<T> => {
  try {
    let data = JSON.stringify({
      mode,
      ...payload
    })

    let config = {
      method: 'post',
      maxBodyLength: Infinity,
      url: API_URL,
      headers: {
        'Content-Type': 'application/json'
      },
      data
    }

    const response = await axios.request(config)

    return response.data
  } catch (error) {
    console.error('API request failed:', error)
    throw error
  }
}

// Register an account
export const registerAccount = async (
  payload: RegisterPayload
): Promise<any> => {
  return apiRequest('register_account', payload)
}

// Login user
export const login = async (payload: LoginPayload): Promise<LoginResponse> => {
  return apiRequest<LoginResponse>('login', payload)
}

// Fetch announcements
export const fetchAnnouncements = async (): Promise<Announcement[]> => {
  return apiRequest<Announcement[]>('fetch_announcements')
}

// Fetch ongoing incidents or events
export const fetchOngoing = async (id?: string): Promise<OngoingEvent[]> => {
  return apiRequest<OngoingEvent[]>('fetch_ongoing', { id })
}

// Fetch contact information
export const fetchContact = async (): Promise<Contact[]> => {
  return apiRequest<Contact[]>('fetch_contact')
}

// Fetch account information
export const fetchAccount = async (payload: {
  username: string
}): Promise<AccountInfo> => {
  return apiRequest<AccountInfo>('fetch_account', {
    username: payload.username
  })
}

// Mobile locate
export const mobileLocate = async (
  payload: MobileLocatePayload
): Promise<any> => {
  return apiRequest('mobile_locate', payload)
}

// Fetch mobile respond (NEW FUNCTION)
export const fetchMobileRespond = async (
  payload?: MobileRespondPayload
): Promise<MobileRespondResponse> => {
  return apiRequest<MobileRespondResponse>('fetch_mobile_respond', payload)
}

// Fetch ongoing and completed incidents or events
export const fetchOngoingCompleted = async (): Promise<OngoingEvent[]> => {
  return apiRequest<OngoingEvent[]>('fetch_ongoing_completed')
}

// Check mobile response for an ongoing event (NEW FUNCTION)
export const checkMobileRespondOngoing = async (
  payload?: Omit<MobileRespondPayload, 'ongoingID'>
): Promise<any> => {
  return apiRequest('check_mobile_respond_ongoing', payload)
}

// Mobile respond (NEW FUNCTION)
export const mobileRespond = async (
  payload: MobileRespond
): Promise<MobileRespondResponse> => {
  return apiRequest<MobileRespondResponse>('mobile_respond', payload)
}

export const fetchFireResponderAccount = async (
  username?: string
): Promise<AccountInfo> => {
  return apiRequest<AccountInfo>('fetch_fireresponderacc', { username })
}

export const fetchProfile = async (
  isResponder: boolean
): Promise<AccountInfo> => {
  return apiRequest<AccountInfo>(
    isResponder ? 'fetch_fireresponderacc' : 'fetch_pasigresident',
    {}
  )
}

export const fetchStatus = async (): Promise<AccountInfo> => {
  return apiRequest<AccountInfo>('fetch_status', {})
}

export const fetchBarangay = async (): Promise<AccountInfo> => {
  return apiRequest<AccountInfo>('fetch_barangay', {})
}

export const fetchTruck = async (barangay: string) => {
  return apiRequest('fetch_truck', {
    barangay
  })
}

export const patchStatus = async ({ OngoingID, status }) => {
  return apiRequest('update_status', {
    OngoingID,
    status
  })
}

export const returnToBase = async ({ username, ongoingID }) => {
  return apiRequest('ReturnToBase', {
    ongoingID,
    username
  })
}

export const fireOut = async ({ username, date, OngoingID }) => {
  return apiRequest('Fire_Out', {
    username,
    date,
    OngoingID
  })
}

export const cancelRespond = async ({ username, ongoingID }) => {
  return apiRequest('Cancel', {
    username,
    ongoingID
  })
}

export const sendRequest = async ({
  barangay,
  username,
  request,
  ongoingID
}) => {
  return apiRequest('request', {
    barangay,
    username,
    request,
    ongoingID
  })
}

export const editProfile = async (payload, isResponder) => {
  return apiRequest(
    isResponder ? 'update_fireresponderacc' : 'update_pasigresident',
    payload
  )
}

export const addToken = async (payload: {
  username: string
  token: string
}) => {
  return apiRequest('addNotificationToken', payload)
}

export const deleteToken = async (payload: {
  username: string
  token: string
}) => {
  return apiRequest('deleteNotificationToken', payload)
}

export const deleteAccount = async (payload: {
  emailUsername: string
  password: string
}) => {
  return apiRequest('deleteAccount', payload)
}
