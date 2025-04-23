import { editProfile, fetchProfile } from '@/api/api'
import { barangays } from '@/constants/Barangays'
import { setUserInfo } from '@/stores/userSlice'
import { router, useNavigation } from 'expo-router'
import * as ImagePicker from 'expo-image-picker'
import React, { useEffect, useState } from 'react'
import {
  View,
  StyleSheet,
  Image,
  ScrollView,
  Alert,
  TouchableOpacity
} from 'react-native'
import { Text, Button, Divider, TextInput } from 'react-native-paper'
import { useDispatch, useSelector } from 'react-redux'
import DateTimePicker from 'react-native-modal-datetime-picker'
import { format } from 'date-fns'
import { FlatList } from 'react-native-gesture-handler'
import { MaterialCommunityIcons } from '@expo/vector-icons'

const EditProfile = () => {
  const dispatch = useDispatch()
  const navigation = useNavigation()
  const userInfo = useSelector((state) => state?.user?.userInfo)

  const [name, setName] = useState(userInfo?.fullName || '')
  const [email, setEmail] = useState(userInfo?.email || '')
  const [contactNumber, setContactNumber] = useState(
    userInfo?.contactNumber || ''
  )
  const [birthday, setBirthday] = useState(userInfo?.birthday || '')
  const [isDatePickerVisible, setDatePickerVisibility] = useState(false)
  const [isDropdownVisible, setDropdownVisible] = useState(false)
  const [profileImageBase64, setProfileImageBase64] = useState(
    userInfo?.profile || ''
  )
  const [errorMessage, setErrorMessage] = useState('')
  const [passwordErrors, setPasswordErrors] = useState([])
  const [isPasswordVisible, setPasswordVisible] = useState(false)
  const [isConfirmPasswordVisible, setConfirmPasswordVisible] = useState(false)
  const [modalVisible, setModalVisible] = useState(false)
  const [password, setPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')

  const handleBarangaySelect = (option) => {
    setDropdownVisible(false)
    setBarangay(option)
  }

  const fetchBarangayName = (barangay) => {
    return barangay
      ?.toLowerCase()
      ?.replace('brgy_', 'barangay_') // Normalize the prefix
      ?.split('_') // Split by underscores
      ?.map((word) => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize each word
      ?.join(' '); // Join with spaces
  };

  const [barangay, setBarangay] = useState(userInfo?.barangay || '')

  useEffect(() => {
    async function fetch() {
      const response = await fetchProfile(userInfo?.isResponder)
      const user = response?.data?.[
        userInfo?.isResponder ? 'responders' : 'residents'
      ]?.find((user) => user.username === userInfo?.username)

      dispatch(setUserInfo({ ...userInfo, ...user }))
    }

    fetch()
  }, [userInfo?.isResponder])

  const validatePassword = (password) => {
    const errors = []
    if (password.length < 8) errors.push('Must be at least 8 characters')
    if (!/[0-9]/.test(password)) errors.push('Must contain a number')
    if (!/[a-z]/.test(password)) errors.push('Must contain a lowercase letter')
    if (!/[A-Z]/.test(password)) errors.push('Must contain an uppercase letter')
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password))
      errors.push('Must contain a symbol')

    setPasswordErrors(errors)
  }

  const handlePasswordChange = (value) => {
    setPassword(value)
    validatePassword(value)
  }

  const togglePasswordVisibility = () => {
    setPasswordVisible(!isPasswordVisible)
  }

  const toggleConfirmPasswordVisibility = () => {
    setConfirmPasswordVisible(!isPasswordVisible)
  }

  const showDatePicker = () => {
    setDatePickerVisibility(true)
  }

  const hideDatePicker = () => {
    setDatePickerVisibility(false)
  }

  const handleConfirm = (date) => {
    setBirthday(format(date, 'yyyy-MM-dd')) // Format and set the date
    hideDatePicker()
  }

  const handlePickImage = async () => {
    const permissionResult =
      await ImagePicker.requestMediaLibraryPermissionsAsync()
    if (!permissionResult.granted) {
      Alert.alert(
        'Permission required',
        'Permission to access gallery is needed.'
      )
      return
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      base64: true, // Return the image as a base64 string
      quality: 0.7 // Optional: Compress the image to reduce size
    })

    if (!result.canceled) {
      setProfileImageBase64(
        `data:image/jpg;base64,${result?.assets?.[0]?.base64}`
      )
    }
  }

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <TouchableOpacity
        style={styles.closeButton}
        onPress={() => router.back()}
      >
        <MaterialCommunityIcons name="arrow-left" size={40} />
      </TouchableOpacity>
      <TouchableOpacity onPress={handlePickImage}>
        <Image
          style={styles.profileImage}
          source={
            profileImageBase64
              ? { uri: profileImageBase64 }
              : require('@/assets/images/logo.png') // Default image
          }
        />
      </TouchableOpacity>

      <Text style={styles.headerText}>
        {userInfo?.fullName || 'Edit Profile'}
      </Text>
      <Text style={styles.subHeaderText}>
        {userInfo?.isResponder ? 'Responder' : 'Pasig Resident'}
      </Text>

      <Divider style={styles.divider} />

      <View style={styles.infoRow}>
        <TextInput
          mode="flat"
          label="Full Name"
          value={name}
          onChangeText={setName}
          style={styles.input}
          underlineColor="#002f6c"
          activeUnderlineColor="#002f6c"
        />
      </View>

      <View style={styles.infoRow}>
        <TextInput
          mode="flat"
          label="Email Address"
          value={email}
          onChangeText={setEmail}
          style={styles.input}
          underlineColor="#002f6c"
          activeUnderlineColor="#002f6c"
        />
      </View>

      <View style={styles.infoRow}>
        <TextInput
          mode="flat"
          label="Contact Number"
          value={contactNumber}
          onChangeText={setContactNumber}
          style={styles.input}
          underlineColor="#002f6c"
          activeUnderlineColor="#002f6c"
        />
      </View>

      <TouchableOpacity style={styles.infoRow} onPress={showDatePicker}>
        <TextInput
          mode="flat"
          label="Birthday"
          value={birthday}
          editable={false} // Make it non-editable
          style={styles.input}
          underlineColor="#002f6c"
          activeUnderlineColor="#002f6c"
        />
      </TouchableOpacity>

      <DateTimePicker
        isVisible={isDatePickerVisible}
        mode="date"
        onConfirm={handleConfirm}
        onCancel={hideDatePicker}
        maximumDate={
          new Date(new Date().setFullYear(new Date().getFullYear() - 13))
        } // Prevent selecting future dates
      />

      <View style={styles.infoRow}>
        <TouchableOpacity
          style={[styles.input, styles.dropdown]}
          onPress={() => setDropdownVisible(!isDropdownVisible)}
        >
          <Text style={{ color: !barangay ? 'black' : 'black', fontSize: 16 }}>
            {fetchBarangayName(barangay).split('_').join(' ') || 'Select Barangay'}
          </Text>
        </TouchableOpacity>

        {isDropdownVisible && (
          <View style={[styles.dropdownOptionsContainer, { maxHeight: 120 }]}>
            <FlatList
              data={barangays}
              keyExtractor={(item, index) => index.toString()}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.dropdownOption}
                  onPress={() => handleBarangaySelect(item)}
                >
                  <Text style={{ color: 'black' }}>
                    {item.split('_').join(' ')}
                  </Text>
                </TouchableOpacity>
              )}
              nestedScrollEnabled={true} // Enables scrolling within a nested FlatList
            />
          </View>
        )}

        <View style={{ position: 'relative', width: '100%', marginBottom: 20 }}>
          <TextInput
            mode="flat"
            label="Password"
            placeholder="Enter your password"
            value={password}
            onChangeText={handlePasswordChange}
            secureTextEntry={!isPasswordVisible}
            autoCapitalize="none"
            underlineColor="#002f6c"
            activeUnderlineColor="#002f6c"
            textColor="black"
            style={styles.input}
          />
          <TouchableOpacity
            onPress={togglePasswordVisibility}
            style={{ position: 'absolute', right: 10, top: 15 }}
          >
            <MaterialCommunityIcons
              name={isPasswordVisible ? 'eye' : 'eye-off'}
              size={24}
              color="black"
            />
          </TouchableOpacity>
        </View>

        {!!password && passwordErrors.length > 0 && (
          <View style={styles.helperTextContainer}>
            {passwordErrors.map((error, index) => (
              <Text key={index} style={styles.helperText}>
                {error}
              </Text>
            ))}
          </View>
        )}

        <View style={{ position: 'relative', width: '100%' }}>
          <TextInput
            mode="flat"
            label="Confirm Password"
            placeholder="Confirm your password"
            value={confirmPassword}
            onChangeText={setConfirmPassword}
            secureTextEntry={!isConfirmPasswordVisible}
            autoCapitalize="none"
            underlineColor="#002f6c"
            activeUnderlineColor="#002f6c"
            textColor="black"
            style={styles.input}
          />
          <TouchableOpacity
            onPress={toggleConfirmPasswordVisibility}
            style={{ position: 'absolute', right: 10, top: 15 }}
          >
            <MaterialCommunityIcons
              name={isPasswordVisible ? 'eye' : 'eye-off'}
              size={24}
              color="black"
            />
          </TouchableOpacity>
        </View>
        {errorMessage ? (
          <Text style={styles.helperText}>{errorMessage}</Text>
        ) : null}
      </View>

      <Divider style={styles.divider} />

      <Button
        mode="contained"
        style={styles.updateButton}
        onPress={async () => {
          try {
            const payload = {
              username: userInfo?.username,
              updates: {
                barangay: barangay || userInfo?.barangay,
                name: name || userInfo?.fullName,
                emailAddress: email || userInfo?.email,
                password: password || userInfo?.password,
                birthday: new Date(birthday) || userInfo?.birthday,
                profile: profileImageBase64 || userInfo?.photo,
                contactNumber: contactNumber || userInfo?.contactNumber
              }
            }

            const response = await editProfile(payload, userInfo?.isResponder)

            console.log(JSON.stringify({response}, null, 2))
            console.log(JSON.stringify({payload}, null, 2))


            Alert.alert(
              'Profile Updated',
              'Your profile has been updated successfully.'
            )

            router?.back()
          } catch (error) { 


            if (error?.message === 'Account updated successfully') {
              Alert.alert(
                'Profile Updated',
                'Your profile has been updated successfully.'
              )
              router.back()
              return
            }

            Alert.alert(
              'Profile Update Failed',
              error?.response?.data?.messages?.[0] || 'Profile Update failed. Please try again.'
            )
          }
        }}
      >
        Save
      </Button>
    </ScrollView>
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
  headerText: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
    color: '#333',
    textAlign: 'center'
  },
  subHeaderText: {
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
  input: {
    backgroundColor: '#fff',
    fontSize: 16,
    borderRadius: 5
  },
  updateButton: {
    width: '90%',
    marginVertical: 10,
    backgroundColor: '#002f6c',
    paddingVertical: 10,
    borderRadius: 8
  },
  divider: {
    width: '90%',
    marginVertical: 20
  },
  dropdownOptionsContainer: {
    backgroundColor: 'white',
    borderRadius: 5,
    width: '80%',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 5,
    elevation: 5, // For Android
    marginBottom: 10,
    marginLeft: 10
  },
  dropdownOption: {
    padding: 10
  },
  dropdown: {
    borderBottomColor: '#002f6c',
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderRadius: 5,
    padding: 15,
    marginBottom: 20,
    backgroundColor: 'white'
  },
  closeButton: {
    padding: 10,
    marginBottom: 10,
    position: 'absolute',
    left: 10,
    top: 10
  },
  errorMessage: {
    color: 'red',
    fontSize: 14,
    marginBottom: 10,
    textAlign: 'center'
  },
  helperTextContainer: { marginBottom: 10 },
  helperText: { fontSize: 12, color: 'red' }
})

export default EditProfile
