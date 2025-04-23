import { registerAccount } from '@/api/api'
import { MaterialCommunityIcons } from '@expo/vector-icons'
import { useRouter } from 'expo-router'
import React, { useEffect, useState } from 'react'
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ImageBackground,
  Image,
  ScrollView,
  KeyboardAvoidingView,
  Platform,
  Alert,
  FlatList,
  Keyboard
} from 'react-native'
import { TextInput } from 'react-native-paper' // Ensure you have Dropdown imported if using a compatible library or create a custom dropdown
import { useDispatch } from 'react-redux'
import { barangays } from '@/constants/Barangays'
import DateTimePicker from 'react-native-modal-datetime-picker'
import { format, set } from 'date-fns' // Optional: For formatting the date
import { Animated } from 'react-native'

const SignupScreen = () => {
  const [emailOrPhone, setEmailOrPhone] = useState('')
  const [fullName, setFullName] = useState('')
  const [username, setUsername] = useState('')
  const [barangay, setBarangay] = useState('') // New state for selected barangay
  const [password, setPassword] = useState('')
  const [contactNumber, setContactNumber] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [errorMessage, setErrorMessage] = useState('')
  const [passwordErrors, setPasswordErrors] = useState([])
  const [isPasswordVisible, setPasswordVisible] = useState(false)
  const [isConfirmPasswordVisible, setConfirmPasswordVisible] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [emailError, setEmailError] = useState('')
  const [isDropdownVisible, setDropdownVisible] = useState(false)
  const [birthday, setBirthday] = useState('')
  const [isDatePickerVisible, setDatePickerVisibility] = useState(false)
  const [isKeyboardVisible, setKeyboardVisible] = useState(false)
  const [scaleValue] = useState(new Animated.Value(1))

  const router = useRouter()

  useEffect(() => {
    const showSubscription = Keyboard.addListener('keyboardDidShow', () =>
      setKeyboardVisible(true)
    )
    const hideSubscription = Keyboard.addListener('keyboardDidHide', () =>
      setKeyboardVisible(false)
    )

    return () => {
      showSubscription.remove()
      hideSubscription.remove()
    }
  }, [])

  useEffect(() => {
    Animated.timing(scaleValue, {
      toValue: isKeyboardVisible ? 0.8 : 1, // Scale down to 80% when keyboard is visible
      duration: 300, // Animation duration in milliseconds
      useNativeDriver: true // Optimize with native driver
    }).start()
  }, [isKeyboardVisible])

  const handleBarangaySelect = (option) => {
    setDropdownVisible(false)
    setBarangay(option)
  }

  // Show date picker
  const showDatePicker = () => {
    setDatePickerVisibility(true)
  }

  // Hide date picker
  const hideDatePicker = () => {
    setDatePickerVisibility(false)
  }

  // Handle date picked
  const handleConfirm = (date) => {
    setBirthday(format(date, 'MM/dd/yyyy')) // Set and format the selected date
    hideDatePicker() // Close the picker
  }

  useEffect(() => {
    if (confirmPassword && confirmPassword !== password) {
      setErrorMessage('Passwords do not match')
    } else {
      setErrorMessage('')
    }
  }, [confirmPassword, password])

  const validateEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(email)) {
      setEmailError('Invalid email format')
    } else {
      setEmailError('')
    }
  }

  const handleEmailChange = (value) => {
    setEmailOrPhone(value)
    validateEmail(value)
  }

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
    setConfirmPasswordVisible(!isConfirmPasswordVisible)
  }

  const handleRegister = async () => {
    setIsLoading(true)
    setErrorMessage('')

    const payload = {
      barangay,
      email: emailOrPhone,
      fullName,
      username,
      contactNumber,
      birthday,
      password,
      repeatPassword: confirmPassword
    }

    try {
      const response = await registerAccount(payload)
      Alert.alert(
        'Registration Successful',
        'To activate your account, please check your email to verify your account.'
      )
      setBarangay('')
      setEmailOrPhone('')
      setFullName('')
      setUsername('')
      setBirthday('')
      setPassword('')
      setConfirmPassword('')
      setContactNumber('')
      setErrorMessage('')
      router.push('/login')
    } catch (error) {
      const message =
        error.response.data.messages[0] ||
        'Registration failed. Please try again.'
      Alert.alert('Registration Failed', message)
    } finally {
      setIsLoading(false)
    }
  }

  const isFormIncomplete =
    isLoading ||
    passwordErrors.length > 0 ||
    !emailOrPhone ||
    !fullName ||
    !username ||
    !barangay ||
    !password ||
    !confirmPassword

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ImageBackground
        source={require('../assets/images/bg.jpg')}
        style={styles.backgroundImage}
      >
        <View style={styles.formContainer}>
          <ScrollView
            style={{ height: '60%' }}
            showsVerticalScrollIndicator={false}
          >
            <View style={styles.header}>
              <Text style={styles.welcomeText}>Hello Pasigueno</Text>
              <Text style={styles.subText}>Let's create an account</Text>
            </View>

            <TextInput
              mode="flat"
              label="Email"
              placeholder="Enter your email"
              value={emailOrPhone}
              onChangeText={handleEmailChange}
              keyboardType="email-address"
              autoCapitalize="none"
              style={styles.input}
              underlineColor="#002f6c"
              activeUnderlineColor="#002f6c"
              textColor="black"
            />
            {emailError ? (
              <Text style={styles.helperText}>{emailError}</Text>
            ) : null}

            <TextInput
              mode="flat"
              label="Full Name"
              placeholder="Enter your full name"
              value={fullName}
              onChangeText={setFullName}
              autoCapitalize="words"
              style={styles.input}
              underlineColor="#002f6c"
              activeUnderlineColor="#002f6c"
              textColor="black"
            />

            <TextInput
              mode="flat"
              label="Username"
              placeholder="Enter your username"
              value={username}
              onChangeText={setUsername}
              autoCapitalize="none"
              style={styles.input}
              underlineColor="#002f6c"
              activeUnderlineColor="#002f6c"
              textColor="black"
            />

            <TextInput
              mode="flat"
              label="Contact Number"
              value={contactNumber}
              onChangeText={setContactNumber}
              style={styles.input}
              underlineColor="#002f6c"
              activeUnderlineColor="#002f6c"
              keyboardType='phone-pad'
            />
            <TouchableOpacity
              style={[styles.input, styles.dropdown]}
              onPress={showDatePicker}
            >
              <Text
                style={{ color: birthday ? 'black' : 'gray', fontSize: 16 }}
              >
                {birthday || 'Select Birthday'}
              </Text>
            </TouchableOpacity>

            <DateTimePicker
              isVisible={isDatePickerVisible}
              mode="date"
              onConfirm={handleConfirm}
              onCancel={hideDatePicker}
              maximumDate={
                new Date(new Date().setFullYear(new Date().getFullYear() - 13))
              }
            />

            <TouchableOpacity
              style={[styles.input, styles.dropdown]}
              onPress={() => setDropdownVisible(!isDropdownVisible)}
            >
              <Text
                style={{ color: !barangay ? 'black' : 'black', fontSize: 16 }}
              >
                {barangay.split('_').join(' ') || 'Select Barangay'}
              </Text>
            </TouchableOpacity>

            {isDropdownVisible && (
              <View
                style={[styles.dropdownOptionsContainer, { maxHeight: 120 }]}
              >
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
            <View style={{ position: 'relative', width: '100%' }}>
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
                style={{ position: 'absolute', right: 10, top: 25 }}
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
                style={{ position: 'absolute', right: 10, top: 25 }}
              >
                <MaterialCommunityIcons
                  name={isConfirmPasswordVisible ? 'eye' : 'eye-off'}
                  size={24}
                  color="black"
                />
              </TouchableOpacity>
            </View>
            {errorMessage ? (
              <Text style={styles.helperText}>{errorMessage}</Text>
            ) : null}

            <TouchableOpacity
              style={[styles.loginButton, isFormIncomplete && { opacity: 0.5 }]}
              onPress={handleRegister}
              disabled={isFormIncomplete}
            >
              <Text style={styles.loginButtonText}>
                {isLoading ? 'Signing up...' : 'Sign up'}
              </Text>
            </TouchableOpacity>
            <View
              style={{
                justifyContent: 'center',
                alignItems: 'center',
                flexDirection: 'row',
                gap: 10,
                marginBottom: 20
              }}
            >
              <Text
                style={{
                  color: 'gray',
                  fontSize: 16
                }}
              >
                Have an account?
              </Text>
              <TouchableOpacity onPress={() => router.back()}>
                <Text
                  style={{ fontWeight: 'bold', color: '#002f6c', fontSize: 16 }}
                >
                  Log in
                </Text>
              </TouchableOpacity>
            </View>
          </ScrollView>
        </View>

        <View style={styles.logoContainer}>
          <Animated.Image
            source={require('../assets/images/logo.png')}
            style={[styles.logo, { transform: [{ scale: scaleValue }] }]}
          />
        </View>
      </ImageBackground>
    </KeyboardAvoidingView>
  )
}

const styles = StyleSheet.create({
  backgroundImage: {
    flex: 1,
    justifyContent: 'flex-start',
    flexDirection: 'column-reverse',
    tintColor: 'rgba(0, 47, 108, 0.6)'
  },
  dropdown: {
    borderBottomColor: '#002f6c',
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderRadius: 5,
    padding: 15,
    marginBottom: 20,
    backgroundColor: 'white'
  },
  formContainer: {
    backgroundColor: 'white',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 20,
    paddingTop: 40
  },
  header: {
    alignItems: 'flex-start'
  },
  welcomeText: {
    fontSize: 40,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#002f6c' // Pasig Blue
  },
  subText: {
    fontSize: 15,
    textAlign: 'center',
    color: 'grey',
    marginBottom: 30
  },
  input: {
    marginBottom: 20,
    fontSize: 16,
    backgroundColor: 'white',
    color: 'black'
  },
  backButton: {
    position: 'absolute',
    top: 40, // Adjust as needed
    left: 20, // Adjust as needed
    zIndex: 1
  },
  errorMessage: {
    color: 'red',
    fontSize: 14,
    marginBottom: 10,
    textAlign: 'center'
  },
  helperTextContainer: { marginBottom: 10 },
  helperText: { fontSize: 12, color: 'red' },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 30
  },
  label: {
    fontSize: 16,
    color: 'black'
  },
  forgotPassword: {
    fontSize: 16,
    color: '#002f6c' // Pasig Blue
  },
  loginButton: {
    backgroundColor: '#002f6c', // Pasig Blue
    paddingVertical: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginVertical: 20
  },
  loginButtonText: {
    fontSize: 18,
    color: '#fff',
    fontWeight: 'bold'
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center'
  },
  footerText: {
    fontSize: 16,
    color: 'grey'
  },
  signUpText: {
    fontSize: 16,
    color: '#002f6c' // Pasig Blue
  },
  logoContainer: {
    backgroundColor: 'rgba(0, 47, 108, 0.6)',
    paddingVertical: 60,
    alignItems: 'center',
    height: '40%'
  },

  logo: {
    width: '40%', // Scales the logo width
    height: undefined, // Maintains proportional height
    aspectRatio: 1, // Keeps the logo's aspect ratio
    resizeMode: 'contain' // Prevents cropping
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
  }
})

export default SignupScreen
