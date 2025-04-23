import {
  deleteAccount,
  deleteToken,
  fetchAccount,
  fetchFireResponderAccount,
  fetchProfile
} from '@/api/api'
import { barangays } from '@/constants/Barangays'
import { setUserInfo } from '@/stores/userSlice'
import AsyncStorage from '@react-native-async-storage/async-storage'
import { router, useFocusEffect, useNavigation } from 'expo-router'
import React, { useCallback, useEffect, useState } from 'react'
import {
  View,
  StyleSheet,
  Image,
  ScrollView,
  Alert,
  TextInput,
  Modal
} from 'react-native'
import { Text, Button, Divider } from 'react-native-paper'
import { useDispatch, useSelector } from 'react-redux'

const ProfileScreen = () => {
  const dispatch = useDispatch()
  const navigation = useNavigation()
  const userInfo = useSelector((state) => state?.user?.userInfo)
  const [isDeleteDialogVisible, setDeleteDialogVisible] = useState(false)
  const [deleteUsername, setDeleteUsername] = useState('')
  const [deletePassword, setDeletePassword] = useState('')
  const [deleteErrorMessage, setDeleteErrorMessage] = useState('')

  useFocusEffect(
    useCallback(() => {
      let isActive = true // Track if the effect is still active

      async function fetch() {
        try {
          const response = await fetchProfile(userInfo?.isResponder)

          const user = response?.data?.[
            userInfo?.isResponder ? 'responders' : 'residents'
          ]?.find((user) => user.username === userInfo?.username)

          if (!user) return

          let account = {}

          if (userInfo?.isResponder) {
            const response = await fetchFireResponderAccount(userInfo.username)
            account = response?.data?.contacts?.[0]
          } else {
            const response = await fetchAccount({ username: userInfo.username })
            account = response?.data?.contacts?.[0]
          }

          // Only dispatch if the component is still active
          if (isActive) {
            dispatch(setUserInfo({ ...userInfo, ...account, ...user }))
          }
        } catch (error) {
          console.error('Error fetching user info:', error)
        }
      }

      fetch()

      return () => {
        isActive = false // Cleanup on unmount or blur
      }
    }, [userInfo?.isResponder]) // Dependencies to trigger re-run
  )

  const fetchBarangayName = (barangay) => {
    return barangay
      ?.toLowerCase()
      ?.replace('brgy_', 'barangay_') // Normalize the prefix
      ?.split('_') // Split by underscores
      ?.map((word) => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize each word
      ?.join(' ') // Join with spaces
  }

  return (
    <>
      <ScrollView contentContainerStyle={styles.container}>
        <Image
          style={styles.profileImage}
          source={
            userInfo?.profile
              ? { uri: userInfo?.profile }
              : require('@/assets/images/logo.png')
          } // Replace with actual profile picture URL
        />

        <Text style={styles.nameText}>{userInfo?.fullName}</Text>
        <Text style={styles.locationText}>
          {userInfo?.isResponder ? 'Responder' : 'Pasig Resident'}
        </Text>

        <Divider style={styles.divider} />

        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Email Address</Text>
          <Text style={styles.infoText}>{userInfo?.email || '-'}</Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Contact Number</Text>
          <Text style={styles.infoText}>{userInfo?.contactNumber || '-'}</Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Birthday</Text>
          <Text style={styles.infoText}>{userInfo?.birthday || '-'}</Text>
        </View>

        {userInfo?.isResponder && (
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Address</Text>
            <Text style={styles.infoText}>
              {fetchBarangayName(userInfo?.address) || '-'}
            </Text>
          </View>
        )}

        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Barangay</Text>
          <Text style={styles.infoText}>
            {fetchBarangayName(userInfo?.barangay) || '-'}
          </Text>
        </View>

        <Divider style={styles.divider} />

        <Button
          mode="contained"
          style={styles.updateButton}
          onPress={() => {
            router.navigate('/(tabs)/account/editProfile')
          }}
        >
          Update Profile
        </Button>
        <Button
          mode="contained"
          style={[styles.updateButton, { backgroundColor: 'red' }]} // Danger color
          onPress={() => setDeleteDialogVisible(true)}
        >
          Delete Account
        </Button>

        <Button
          mode="contained"
          style={styles.updateButton}
          onPress={() => {
            Alert.alert('Logout', 'Are you sure you want to logout?', [
              { text: 'No', style: 'cancel' },
              {
                text: 'Yes',
                onPress: () => {
                  async function handleLogout() {
                    try {
                      const expoPushToken =
                        await AsyncStorage.getItem('expoPushToken')
                      await deleteToken({
                        username: userInfo?.username,
                        token: expoPushToken ?? ''
                      })

                      console.log(
                        'Token deleted successfully for user:',
                        userInfo.username
                      )
                    } catch (error) {
                      console.error(
                        'Error deleting token:',
                        error?.response?.data?.messages?.[0]
                      )
                    }

                    dispatch(setUserInfo(null))
                    navigation.reset({
                      index: 0,
                      routes: [{ name: 'login' }]
                    })
                  }

                  handleLogout()
                }
              }
            ])
          }}
        >
          Logout
        </Button>
      </ScrollView>
      {isDeleteDialogVisible && (
        <Modal
          transparent={true}
          visible={isDeleteDialogVisible}
          onRequestClose={() => setDeleteDialogVisible(false)} // Close modal on back button press
        >
          <View style={styles.modalOverlay}>
            <View style={styles.dialogContainer}>
              <Text style={styles.dialogTitle}>Delete Account</Text>
              <TextInput
                style={styles.dialogInput}
                placeholder="Username"
                value={deleteUsername}
                onChangeText={setDeleteUsername}
              />
              <TextInput
                style={styles.dialogInput}
                placeholder="Password"
                value={deletePassword}
                onChangeText={setDeletePassword}
                secureTextEntry
              />
              {deleteErrorMessage ? (
                <Text style={styles.errorMessage}>{deleteErrorMessage}</Text>
              ) : null}
              <View style={styles.dialogActions}>
                <Button
                  mode="outlined"
                  onPress={() => setDeleteDialogVisible(false)}
                  style={styles.dialogCancelButton}
                >
                  Cancel
                </Button>
                <Button
                  mode="contained"
                  style={styles.dialogDeleteButton}
                  onPress={async () => {
                    try {
                      const response = await deleteAccount({
                        emailUsername: deleteUsername,
                        password: deletePassword
                      })

                      console.log('Delete account response:', response)

                      Alert.alert('Success', 'Account deleted successfully.', [
                        {
                          text: 'OK',
                          onPress: () => {
                            dispatch(setUserInfo(null))
                            navigation.reset({
                              index: 0,
                              routes: [{ name: 'login' }]
                            })
                          }
                        }
                      ])
                    } catch (error) {
                      console.error('Delete account error:', error)
                      setDeleteErrorMessage(
                        'An error occurred. Please try again.'
                      )
                    }
                  }}
                >
                  Delete
                </Button>
              </View>
            </View>
          </View>
        </Modal>
      )}
    </>
  )
}

const styles = StyleSheet.create({
  container: {
    flexGrow: 1,
    alignItems: 'center',
    padding: 16,
    backgroundColor: '#f5f5f5'
  },
  profileImage: {
    width: 120,
    height: 120,
    borderRadius: 60,
    marginBottom: 16,
    borderColor: '#ccc',
    borderWidth: 2
  },
  nameText: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
    color: '#333',
    textAlign: 'center'
  },
  locationText: {
    fontSize: 16,
    color: '#555',
    marginBottom: 20,
    textAlign: 'center'
  },
  infoRow: {
    marginBottom: 20,
    width: '100%',
    paddingHorizontal: 16
  },
  infoLabel: {
    fontSize: 14,
    color: '#777',
    marginBottom: 4
  },
  infoText: {
    fontSize: 16,
    fontWeight: '500',
    color: '#333'
  },
  updateButton: {
    width: '90%',
    marginVertical: 10,
    backgroundColor: '#1e3d8f',
    paddingVertical: 10,
    borderRadius: 8
  },
  divider: {
    width: '90%',
    marginVertical: 20
  },
  dialogContainer: {
    backgroundColor: 'white',
    padding: 20,
    borderRadius: 8,
    elevation: 5,
    width: '90%',
    alignSelf: 'center',
    marginTop: 20
  },
  dialogTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 20,
    textAlign: 'center'
  },
  dialogInput: {
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 8,
    padding: 10,
    marginBottom: 10,
    width: '100%'
  },
  dialogActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 20
  },
  dialogCancelButton: {
    backgroundColor: '#f0f0f0',
    borderColor: '#ccc'
  },
  dialogDeleteButton: {
    backgroundColor: 'red'
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)', // Semi-transparent background
    justifyContent: 'center',
    alignItems: 'center'
  }
})

export default ProfileScreen
