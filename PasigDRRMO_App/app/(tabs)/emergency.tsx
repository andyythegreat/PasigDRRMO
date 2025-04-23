import React, { useState, useEffect, useRef, useMemo } from 'react'
import {
  StyleSheet,
  Dimensions,
  View,
  Alert,
  Animated,
  Easing,
  Text,
  TextInput,
  TouchableOpacity,
  FlatList,
  Image,
  Modal,
  Pressable,
  ActivityIndicator
} from 'react-native'
import MapView, { Polygon, Marker } from 'react-native-maps'
import BottomSheet from '@gorhom/bottom-sheet'
import * as Location from 'expo-location'
import axios from 'axios'
import { pasigCoordinates } from '@/constants/PasigCoordinates'
import { FontAwesome, MaterialCommunityIcons } from '@expo/vector-icons'
import { mobileLocate } from '@/api/api'
import { useSelector } from 'react-redux'
import { barangays } from '@/constants/Barangays'
import { convertToPHT } from '@/utils/convertTime'
import { useAttachPhoto } from '@/hooks/useAttachPhoto'
import { Camera, CameraView } from 'expo-camera'
import { CameraType } from 'expo-camera/build/legacy/Camera.types'

// export const GOOGLE_MAPS_APIKEY = 'AIzaSyAWBr2bStj01YgrUxnz08lhLFdFUEwoIDA'
export const GOOGLE_MAPS_APIKEY = 'AIzaSyAWBr2bStj01YgrUxnz08lhLFdFUEwoIDA'

// Utility function to check if a point is inside a polygon
const isPointInPolygon = (point, polygon) => {
  let isInside = false
  let minX = polygon[0].latitude,
    maxX = polygon[0].latitude
  let minY = polygon[0].longitude,
    maxY = polygon[0].longitude

  for (let i = 1; i < polygon.length; i++) {
    const q = polygon[i]
    minX = Math.min(q.latitude, minX)
    maxX = Math.max(q.latitude, maxX)
    minY = Math.min(q.longitude, minY)
    maxY = Math.max(q.longitude, maxY)
  }

  if (
    point.latitude < minX ||
    point.latitude > maxX ||
    point.longitude < minY ||
    point.longitude > maxY
  ) {
    return false
  }

  let i = 0,
    j = polygon.length - 1
  for (i, j; i < polygon.length; j = i++) {
    if (
      polygon[i].longitude > point.longitude !==
        polygon[j].longitude > point.longitude &&
      point.latitude <
        ((polygon[j].latitude - polygon[i].latitude) *
          (point.longitude - polygon[i].longitude)) /
          (polygon[j].longitude - polygon[i].longitude) +
          polygon[i].latitude
    ) {
      isInside = !isInside
    }
  }

  return isInside
}

export default function Emergency() {
  const [currentLocation, setCurrentLocation] = useState(null)
  const [loading, setLoading] = useState(true)
  const [selectedLocation, setSelectedLocation] = useState(null)
  const [nearestLandmark, setNearestLandmark] = useState('')
  const [causeOfFire, setCauseOfFire] = useState('Electrical')
  const [placeName, setPlaceName] = useState('')
  const [barangayName, setBarangayName] = useState('')
  const [suggestions, setSuggestions] = useState([])
  const rotateAnimation = useRef(new Animated.Value(0)).current
  const bottomSheetRef = useRef(null)
  const snapPoints = useMemo(() => ['25%', '50%', '90%'], [])
  const [showCauseModal, setShowCauseModal] = useState(false)
  const [errorMessage, setErrorMessage] = useState('')
  const user = useSelector((state) => state.user?.userInfo)
  const [isImageModalVisible, setImageModalVisible] = useState(false)
  const [sendLoading, setSendLoading] = useState(false)

  const {
    photo,
    hasPermission,
    requestCameraPermission,
    capturePhoto,
    clearPhoto
  } = useAttachPhoto()

  const cameraRef = useRef<CameraView>(null)
  const [isCameraVisible, setIsCameraVisible] = useState(false)

  useEffect(() => {
    requestCameraPermission()
  }, [])

  const handleOpenCamera = () => {
    if (!hasPermission) {
      Alert.alert(
        'Permission Denied',
        'Camera access is required to take photos.'
      )
      return
    }
    setIsCameraVisible(true)
  }

  const handleCapturePhoto = async () => {
    if (cameraRef) {
      await capturePhoto(cameraRef)
      setIsCameraVisible(false)
    }
  }

  const renderCamera = () => (
    <Modal visible={isCameraVisible} transparent={false} animationType="slide">
      <CameraView style={{ flex: 1 }} facing={CameraType.back} ref={cameraRef}>
        <View style={styles.cameraControls}>
          <TouchableOpacity
            style={styles.captureButton}
            onPress={handleCapturePhoto}
          >
            <Text
              style={{
                color: 'black',
                fontWeight: 'bold',
                textAlign: 'center'
              }}
            >
              Capture
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={{
              backgroundColor: '#002f6c',
              paddingVertical: 10,
              paddingHorizontal: 20,
              borderRadius: 5,
              position: 'absolute',
              top: 20,
              left: 20
            }}
            onPress={() => setIsCameraVisible(false)}
          >
            <Text style={styles.buttonText}>Close</Text>
          </TouchableOpacity>
        </View>
      </CameraView>
    </Modal>
  )

  //

  const causeOptions = [
    'Not Specified',
    'Electrical',
    'Post',
    'Residential',
    'Commercial',
    'Industrial',
    'Grass',
    'Rubbish',
    'Vehicular'
  ]

  // Animation for loading
  useEffect(() => {
    Animated.loop(
      Animated.timing(rotateAnimation, {
        toValue: 1,
        duration: 2000,
        easing: Easing.linear,
        useNativeDriver: true
      })
    ).start()
  }, [rotateAnimation])

  const rotate = rotateAnimation.interpolate({
    inputRange: [0, 1],
    outputRange: ['0deg', '360deg']
  })

  // Fetch user's current location
  useEffect(() => {
    ;(async () => {
      let { status } = await Location.requestForegroundPermissionsAsync()
      if (status !== 'granted') {
        Alert.alert('Permission to access location was denied')
        setLoading(false)
        return
      }

      let location = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.High
      })

      setCurrentLocation({
        latitude: location.coords.latitude,
        longitude: location.coords.longitude
      })

      setLoading(false)

      Location.watchPositionAsync(
        { accuracy: Location.Accuracy.High, distanceInterval: 1 },
        (newLocation) => {
          setCurrentLocation({
            latitude: newLocation.coords.latitude,
            longitude: newLocation.coords.longitude
          })
        }
      )
    })()
  }, [])

  const handleCauseSelection = (selectedCause) => {
    setCauseOfFire(selectedCause)
    setShowCauseModal(false)
  }

  // Fetch place name and barangay based on coordinates
  const fetchPlaceName = async (latitude, longitude) => {
    try {
      const response = await axios.get(
        `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${GOOGLE_MAPS_APIKEY}`
      )

      if (response.data.results.length > 0) {
        const address = response.data.results[0].formatted_address
        setPlaceName(address)

        const barangay = response.data.results.find((result) =>
          result.address_components.some((component) =>
            component.types.includes('sublocality')
          )
        )

        if (barangay) {
          const barangayName = barangay.address_components.find((component) =>
            component.types.includes('sublocality')
          ).long_name
          setBarangayName(barangayName)
        } else {
          setBarangayName('Unknown Barangay')
        }
      } else {
        setPlaceName('Unknown Location')
        setBarangayName('Unknown Barangay')
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to fetch place name')
    }
  }

  // Fetch place suggestions from Google Places API
  const fetchPlaceSuggestions = async (query) => {
    setPlaceName(query)

    if (query.length === 0) {
      setSuggestions([])
      return
    }

    try {
      const response = await axios.get(
        `https://maps.googleapis.com/maps/api/place/autocomplete/json?input=${query}&key=${GOOGLE_MAPS_APIKEY}&location=14.5995,120.9842&radius=5000`
      )

      const filteredSuggestions = await Promise.all(
        response.data.predictions.map(async (prediction) => {
          const { place_id } = prediction
          const details = await axios.get(
            `https://maps.googleapis.com/maps/api/place/details/json?place_id=${place_id}&key=${GOOGLE_MAPS_APIKEY}`
          )
          const { lat, lng } = details.data.result.geometry.location
          if (
            isPointInPolygon(
              { latitude: lat, longitude: lng },
              pasigCoordinates
            )
          ) {
            return prediction
          }
          return null
        })
      )

      setSuggestions(filteredSuggestions.filter(Boolean).slice(0, 5))
    } catch (error) {
      console.error('Error fetching suggestions:', error)
    }
  }

  // Handle selecting a place suggestion
  const handleSelectSuggestion = async (placeId) => {
    try {
      const response = await axios.get(
        `https://maps.googleapis.com/maps/api/place/details/json?place_id=${placeId}&key=${GOOGLE_MAPS_APIKEY}`
      )

      const { lat, lng } = response.data.result.geometry.location

      if (
        isPointInPolygon({ latitude: lat, longitude: lng }, pasigCoordinates)
      ) {
        setSelectedLocation({ latitude: lat, longitude: lng })
        fetchPlaceName(lat, lng)
        setSuggestions([]) // Clear suggestions after selection
      } else {
        Alert.alert(
          'Invalid Selection',
          'You can only report fires within Pasig City.'
        )
      }
    } catch (error) {
      console.error('Error fetching place details:', error)
    }
  }

  // Handle map press event to set a location
  const handleMapPress = (event) => {
    const { latitude, longitude } = event.nativeEvent.coordinate
    const point = { latitude, longitude }

    if (isPointInPolygon(point, pasigCoordinates)) {
      setSelectedLocation(point) // Set pinned location
      fetchPlaceName(latitude, longitude) // Fetch place name
    } else {
      Alert.alert(
        'Outside Pasig City',
        'You can only report fires within Pasig City.'
      )
    }
  }

  const handleCurrentLocation = (location) => {
    const { latitude, longitude } = location
    const point = { latitude, longitude }

    if (isPointInPolygon(point, pasigCoordinates)) {
      setSelectedLocation(point) // Set pinned location
      fetchPlaceName(latitude, longitude) // Fetch place name
    } else {
      Alert.alert(
        'Outside Pasig City',
        'You can only report fires within Pasig City.'
      )
    }
  }

  const fetchBarangayName = (barangay) => {
    const firstTwoWords = barangay.split(' ').slice(0, 2).join(' ')

    const transformedBarangay = `BRGY_${firstTwoWords.toUpperCase()}`

    if (barangays.includes(transformedBarangay)) {
      return transformedBarangay
    }
    return false
  }

  // Send report function
  const handleSendReport = async () => {
    if (!selectedLocation) {
      setErrorMessage('Please select a location on the map.')
      return
    }

    if (!placeName || placeName === 'Unknown Location') {
      setErrorMessage('Please enter a valid location.')
      return
    }

    const barangay = fetchBarangayName(barangayName)
    if (!barangay) {
      setErrorMessage(`${barangayName} is not a valid barangay in Pasig City.`)
      return
    }

    try {
      setSendLoading(true)
      const payload = {
        date: new Date().toISOString(),
        caller: user.username, // You can replace with a dynamic value if needed
        location: placeName || 'Unknown Location',
        longitude: selectedLocation.longitude,
        latitude: selectedLocation.latitude,
        barangay: barangay || 'Unknown Barangay',
        photo: null,
        involve: causeOfFire,
        status: 'For Verification'
      }

      if (photo?.base64) {
        payload['photo'] = `data:image/jpg;base64,${photo?.base64}`
      }

      await mobileLocate(payload)
      setNearestLandmark('')
      setCauseOfFire('Not Specified')
      setSelectedLocation(null)
      setPlaceName('')
      setErrorMessage('')
      clearPhoto()
      Alert.alert('Success', 'Incident reported successfully.')
    } catch (error) {
      Alert.alert(
        'Thank You',
        error?.response?.data?.messages?.[0] ?? 'Failed to report the incident.'
      )
      console.error('Error reporting incident:', error)
    } finally {
      setSendLoading(false)
    }
  }

  const renderContent = () => (
    <View style={styles.bottomSheetContainer}>
      <Text style={styles.title}>For Verification</Text>
      {errorMessage ? (
        <Text style={styles.errorMessage}>{errorMessage}</Text>
      ) : null}

      <View style={{}}>
        <TextInput
          style={[styles.input, { marginBottom: 5 }]} // Assign 90% space to TextInput
          placeholder="Search Location"
          placeholderTextColor="black"
          onChangeText={fetchPlaceSuggestions}
          value={placeName}
        />
        <TouchableOpacity
          style={{ flexDirection: 'row', marginBottom: 10 }}
          onPress={() => handleCurrentLocation(currentLocation)}
        >
          <FontAwesome
            name="location-arrow"
            color={'black'}
            size={20}
            style={{ marginRight: 5 }} // Assign 10% space to FontAwesome
          />
          <Text>Use my current location</Text>
        </TouchableOpacity>
      </View>
      {suggestions.length > 0 && (
        <FlatList
          data={suggestions}
          renderItem={({ item }) => (
            <TouchableOpacity
              onPress={() => handleSelectSuggestion(item.place_id)}
            >
              <Text style={styles.suggestion}>{item.description}</Text>
            </TouchableOpacity>
          )}
          keyExtractor={(item) => item.place_id}
        />
      )}

      <TextInput
        style={styles.input}
        placeholder="Nearest Landmark"
        placeholderTextColor="black"
        value={nearestLandmark}
        onChangeText={setNearestLandmark}
      />

      {/* Cause of Fire - Trigger modal */}
      <TouchableOpacity
        style={styles.input}
        onPress={() => setShowCauseModal(true)}
      >
        <Text>{causeOfFire}</Text>
      </TouchableOpacity>

      {/* Modal for selecting Cause of Fire */}
      <Modal
        visible={showCauseModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowCauseModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Select Cause of Fire</Text>
            {causeOptions.map((option, index) => (
              <TouchableOpacity
                key={index}
                onPress={() => handleCauseSelection(option)}
                style={styles.modalOption}
              >
                <Text style={styles.modalOptionText}>{option}</Text>
              </TouchableOpacity>
            ))}

            <TouchableOpacity
              onPress={() => setShowCauseModal(false)}
              style={styles.closeButton}
            >
              <Text style={styles.closeButtonText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
      {photo?.base64 && (
        <>
          {/* Photo Preview */}
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'center',
              alignItems: 'center',
              gap: 2
            }}
          >
            <TouchableOpacity onPress={() => setImageModalVisible(true)}>
              <Image
                source={{ uri: `data:image/jpg;base64,${photo?.base64}` }}
                style={{ width: 100, height: 100, marginBottom: 10 }}
                resizeMode="contain"
              />
            </TouchableOpacity>
            <TouchableOpacity onPress={() => clearPhoto()}>
              <Text style={{ fontWeight: '700', color: 'red' }}>
                Remove Photo
              </Text>
            </TouchableOpacity>
          </View>
          <Modal
            visible={photo?.base64 && isImageModalVisible}
            transparent={true}
            animationType="fade"
            onRequestClose={() => setImageModalVisible(false)} // Close modal on request
          >
            <Pressable
              style={styles.modalContainer}
              onPress={() => setImageModalVisible(false)}
            >
              <TouchableOpacity
                style={styles.modalOverlay}
                onPress={() => setImageModalVisible(false)}
              />

              <Image
                source={{ uri: `data:image/jpg;base64,${photo?.base64}` }}
                style={styles.fullScreenImage}
                resizeMode="contain"
              />
            </Pressable>
          </Modal>
        </>
      )}

      <View style={styles.buttonContainer}>
        <TouchableOpacity style={styles.button} onPress={handleOpenCamera}>
          <Text style={styles.buttonText}>Take Photo</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.button, sendLoading ? { opacity: 0.4 } : {}]}
          onPress={handleSendReport}
          disabled={sendLoading}
        >
          {sendLoading ? (
            <ActivityIndicator color="white" size={20} />
          ) : (
            <Text style={styles.buttonText}>Send</Text>
          )}
        </TouchableOpacity>
      </View>
      {renderCamera()}
    </View>
  )

  if (loading) {
    return (
      <View style={styles.loaderContainer}>
        <Animated.Image
          source={require('@/assets/images/logo.png')}
          style={[styles.logo, { transform: [{ rotate: rotate }] }]}
        />
      </View>
    )
  }

  return (
    <View style={styles.container}>
      <MapView
        style={styles.map}
        region={{
          latitude: selectedLocation?.latitude ?? 14.574189450438954,
          longitude: selectedLocation?.longitude ?? 121.0801421431244,
          latitudeDelta: selectedLocation?.latitude ? 0.04 : 0.07,
          longitudeDelta: selectedLocation?.longitude ? 0.04 : 0.07
        }}
        showsUserLocation={false}
        loadingEnabled={true}
        onPress={handleMapPress} // Map press event to select location
      >
        <Polygon
          coordinates={pasigCoordinates}
          strokeColor="red"
          fillColor="rgba(170, 74, 68, 0.2)"
          strokeWidth={2}
        />

        {currentLocation && (
          <Marker coordinate={currentLocation}>
            <Image
              source={require('@/assets/images/firetruck.png')}
              style={{ width: 50, height: 50 }}
              resizeMode="contain"
            />
          </Marker>
        )}

        {selectedLocation && (
          <Marker coordinate={selectedLocation} title="Selected Fire Location">
            <MaterialCommunityIcons name="fire" size={40} color="red" />
          </Marker>
        )}
      </MapView>

      <BottomSheet
        ref={bottomSheetRef}
        index={1}
        snapPoints={snapPoints}
        style={styles.bottomSheet}
      >
        {renderContent()}
      </BottomSheet>
    </View>
  )
}

const styles = StyleSheet.create({
  container: {
    flex: 1
  },
  map: {
    width: Dimensions.get('window').width,
    height: Dimensions.get('window').height
  },
  loaderContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff'
  },
  logo: {
    width: 100,
    height: 100
  },
  bottomSheetContainer: {
    backgroundColor: '#fff',
    padding: 20,
    height: 450
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 20
  },
  input: {
    borderBottomWidth: 1,
    borderColor: '#ccc',
    paddingVertical: 10,
    marginBottom: 20
  },
  suggestion: {
    padding: 10,
    borderBottomColor: '#ccc',
    borderBottomWidth: 1
  },
  buttonContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between'
  },
  button: {
    backgroundColor: '#002f6c',
    padding: 15,
    borderRadius: 8,
    width: '45%',
    alignItems: 'center'
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold'
  },
  errorMessage: {
    color: 'red',
    marginBottom: 10,
    textAlign: 'center'
  },
  modalContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.5)'
  },
  modalContent: {
    width: '80%',
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    alignItems: 'center'
  },
  removeIconContainer: {
    position: 'absolute',
    top: -10,
    right: -10,
    zIndex: 10,
    backgroundColor: 'white',
    borderRadius: 50,
    padding: 2
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 20
  },
  modalOption: {
    paddingVertical: 10,
    width: '100%',
    borderBottomColor: '#ccc',
    borderBottomWidth: 1,
    alignItems: 'center'
  },
  modalOptionText: {
    fontSize: 16
  },
  closeButton: {
    marginTop: 20,
    backgroundColor: '#002f6c',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5
  },
  closeButtonText: {
    color: '#fff',
    fontSize: 16
  },
  imagePreview: {
    width: 100,
    height: 100,
    marginBottom: 10
  },
  removePhotoText: {
    color: 'red',
    textAlign: 'center',
    marginBottom: 10
  },
  viewImageText: {
    color: '#002f6c',
    textDecorationLine: 'underline',
    textAlign: 'center',
    marginVertical: 10
  },
  modalImageContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.8)'
  },
  fullImage: {
    width: '90%',
    height: '70%',
    resizeMode: 'contain'
  },
  cameraControls: {
    flex: 1,
    backgroundColor: 'transparent',
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 20
  },
  captureButton: {
    backgroundColor: 'white',
    padding: 15,
    borderRadius: 50,
    position: 'absolute', // Use absolute positioning
    bottom: 20,
    left: 120,
    right: 120, // Distance from the bottom
    zIndex: 10,
    justifyContent: 'center',
    alignItems: 'center' // Ensure it appears above other elements
  },
  previewImage: {
    width: 100,
    height: 100,
    marginBottom: 10,
    borderRadius: 8
  },

  modalOverlay: {
    position: 'absolute',
    top: 0,
    bottom: 0,
    left: 0,
    right: 0
  },
  fullScreenImage: {
    width: '100%',
    height: '100%'
  }
})
