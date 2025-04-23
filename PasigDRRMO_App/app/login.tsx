import React, { useRef, useState } from 'react'
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ImageBackground,
  Image,
  ScrollView
} from 'react-native'
import { TextInput } from 'react-native-paper'
import { WebView } from 'react-native-webview'
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons'
import { router } from 'expo-router'
import {
  addToken,
  fetchAccount,
  fetchFireResponderAccount,
  login
} from '@/api/api'
import { useDispatch } from 'react-redux'
import { setUserInfo } from '@/stores/userSlice'
import AsyncStorage from '@react-native-async-storage/async-storage'
import { registerForPushNotificationsAsync } from '@/app/index'

const LoginScreen = () => {
  const [emailUsername, setEmailUsername] = useState('')
  const [password, setPassword] = useState('')
  const [isPasswordVisible, setPasswordVisible] = useState(false)
  const [showWebView, setShowWebView] = useState(false) // Toggle WebView visibility
  const [errorMessage, setErrorMessage] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const webViewRef = useRef(null)
  const dispatch = useDispatch()

  const togglePasswordVisibility = () => {
    setPasswordVisible(!isPasswordVisible)
  }

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

  const handleUserChange = async (user) => {
    const expoPushToken = await AsyncStorage.getItem('expoPushToken')

    if (expoPushToken) {
      // Store token in AsyncStorage
      await AsyncStorage.setItem('expoPushToken', expoPushToken)
      console.log('Token stored in AsyncStorage:', expoPushToken)

      // Call addToken API
      try {
        await addToken({ username: user?.username, token: expoPushToken })
        console.log('Token added successfully for user:', user.username)
        return
      } catch (error) {
        console.error(
          'Error adding token:',
          error?.response?.data?.messages?.[0]
        )
      }
    } else {
      const granted = await fetchPushToken()
      if (granted) {
        await handleUserChange(user)
      }
      return
    }
  }

  const handleLogin = async () => {
    setErrorMessage('') // Reset error message
    setIsLoading(true) // Set loading state

    try {
      // Call the login API
      const response = await login({ emailUsername, password })
      const user = response?.data?.login?.[0]

      if (user && user.email) {
        try {
          await handleUserChange(user)
        } catch (error) {
          console.error(
            'Error adding token:',
            error?.response?.data?.messages?.[0]
          )
        }

        let account = {}
        if (user.isResponder) {
          const response = await fetchFireResponderAccount(user.username)
          account = response?.data?.contacts?.[0]
        } else {
          const response = await fetchAccount({ username: user.username })
          account = response?.data?.contacts?.[0]
        }

        // Store user info in Redux store
        dispatch(setUserInfo({ ...user, ...account }))

        // Navigate to home screen after successful login
        router.push('/(tabs)/home')
      } else {
        // Handle login failure
        setErrorMessage('Invalid credentials. Please try again.')
      }
    } catch (error) {
      console.log('Login failed:', error)
      // Set error message in case of failure
      setErrorMessage(
        'Login failed. Please check your credentials and try again.'
      )
    } finally {
      setIsLoading(false) // Stop loading after the request is complete
    }
  }

  const handleForgotPassword = () => {
    setShowWebView(true) // Show WebView when "Forgot Password" is clicked
  }

  return showWebView ? (
    <View style={{ flex: 1 }}>
      {/* Header */}
      <View style={styles.headerWebView}>
        <TouchableOpacity onPress={() => setShowWebView(false)}>
          <Ionicons name="arrow-back" size={24} color="black" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Forgot Password</Text>
      </View>

      {/* WebView */}
      <WebView
        ref={webViewRef}
        source={{ uri: 'https://pasigdrrmo.site/MForgotPass.php' }} // Replace with your Forgot Password page URL
        style={{ flex: 1 }}
      />
    </View>
  ) : (
    <ImageBackground
      source={require('../assets/images/bg.jpg')}
      style={styles.backgroundImage}
    >
      <ScrollView style={styles.formContainer}>
        <View style={styles.header}>
          <Text style={styles.welcomeText}>Welcome</Text>
          <Text style={styles.subText}>Please login with your information</Text>
        </View>

        <TextInput
          mode="flat"
          label="Email or Username"
          placeholder="Enter your email or username"
          value={emailUsername}
          onChangeText={setEmailUsername}
          keyboardType="email-address"
          autoCapitalize="none"
          style={styles.input}
          underlineColor="#002f6c"
          activeUnderlineColor="#002f6c"
          textColor="black"
        />

        <View style={{ position: 'relative', width: '100%' }}>
          <TextInput
            mode="flat"
            label="Password"
            placeholder="Enter your password"
            value={password}
            onChangeText={setPassword}
            secureTextEntry={!isPasswordVisible}
            autoCapitalize="none"
            underlineColor="#002f6c"
            activeUnderlineColor="#002f6c"
            textColor="black"
            style={styles.input}
          />
          <TouchableOpacity
            onPress={togglePasswordVisibility}
            style={{
              position: 'absolute',
              right: 10,
              top: 25
            }}
          >
            <MaterialCommunityIcons
              name={isPasswordVisible ? 'eye' : 'eye-off'}
              size={24}
              color="black"
            />
          </TouchableOpacity>
        </View>

        {/* Error Message */}
        {errorMessage ? (
          <Text style={styles.errorMessage}>{errorMessage}</Text> // Display error message if exists
        ) : null}

        {/* Remember Me and Forgot Password */}
        <View style={styles.row}>
          <TouchableOpacity onPress={handleForgotPassword}>
            <Text style={styles.forgotPassword}>Forgot your Password?</Text>
          </TouchableOpacity>
        </View>

        <TouchableOpacity style={styles.loginButton} onPress={handleLogin}>
          <Text style={styles.loginButtonText}>LOGIN</Text>
        </TouchableOpacity>

        {/* Sign Up Link */}
        <View style={styles.footer}>
          <Text style={styles.footerText}>Don't have an account?</Text>
          <TouchableOpacity
            onPress={() => {
              router.push('/signup')
            }}
          >
            <Text style={styles.signUpText}> Sign up</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Logo */}
      <View style={styles.logoContainer}>
        <Image
          source={require('../assets/images/logo.png')}
          style={styles.logo}
        />
      </View>
    </ImageBackground>
  )
}

const styles = StyleSheet.create({
  backgroundImage: {
    flex: 1,
    justifyContent: 'flex-start',
    flexDirection: 'column-reverse',
    tintColor: 'rgba(0, 47, 108, 0.6)'
  },
  formContainer: {
    backgroundColor: 'white',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 20,
    paddingTop: 40,
    flex: 1
  },
  headerWebView: {
    marginBottom: 20,
    alignItems: 'center',
    justifyContent: 'flex-start',
    flexDirection: 'row',
    paddingLeft: 20,
    paddingTop: 50
  },
  header: {
    marginBottom: 30,
    alignItems: 'flex-start'
  },
  welcomeText: {
    fontSize: 40,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#002f6c'
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
  errorMessage: {
    color: 'red',
    fontSize: 14,
    marginBottom: 10,
    textAlign: 'center'
  },
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
    color: '#002f6c'
  },
  loginButton: {
    backgroundColor: '#002f6c',
    paddingVertical: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 20
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
    color: '#002f6c', // Pasig Blue
    fontWeight: 'bold'
  },
  logoContainer: {
    backgroundColor: 'rgba(0, 47, 108, 0.6)',
    paddingVertical: 60,
    alignItems: 'center'
  },
  logo: {
    width: 200,
    height: 200,
    marginBottom: 20
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginLeft: 10
  }
})

export default LoginScreen
