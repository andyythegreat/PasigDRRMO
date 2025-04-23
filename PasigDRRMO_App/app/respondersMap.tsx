import React, { useState, useEffect, useRef, useMemo } from 'react'
import {
  StyleSheet,
  Dimensions,
  View,
  Alert,
  Animated,
  Easing,
  Image,
  Text,
  TextInput,
  TouchableOpacity,
  Modal,
  Button
} from 'react-native'
import MapView, { Polygon, Marker } from 'react-native-maps'
import MapViewDirections from 'react-native-maps-directions'
import BottomSheet from '@gorhom/bottom-sheet' // Import BottomSheet
import * as Location from 'expo-location'
import axios from 'axios'
import { pasigCoordinates } from '@/constants/PasigCoordinates'

export const GOOGLE_MAPS_APIKEY = 'AIzaSyAWBr2bStj01YgrUxnz08lhLFdFUEwoIDA'
// 'AIzaSyAWBr2bStj01YgrUxnz08lhLFdFUEwoIDA'; // Replace with your Google Maps API Key

export default function Emergency() {
  const [currentLocation, setCurrentLocation] = useState(null)
  const [loading, setLoading] = useState(true) // Add loading state
  const [mapVisible, setMapVisible] = useState(false) // Modal for map visibility
  const [causeModalVisible, setCauseModalVisible] = useState(false) // Modal for Cause of Fire
  const [selectedLocation, setSelectedLocation] = useState(null)
  const [nearestLandmark, setNearestLandmark] = useState('')
  const [causeOfFire, setCauseOfFire] = useState('Electrical')
  const rotateAnimation = useRef(new Animated.Value(0)).current // Rotation animation value for loader
  const bottomSheetRef = useRef(null) // Ref for BottomSheet
  const [placeName, setPlaceName] = useState('') // Store the place name of the pinned location
  const snapPoints = useMemo(() => ['25%', '50%', '90%'], []) // Snap points for BottomSheet

  // Pasig City location for directions
  const pasigLocation = {
    latitude: 35.6938,
    longitude: 139.7034
  }

  // Start the rotation animation when the component is mounted
  useEffect(() => {
    Animated.loop(
      Animated.timing(rotateAnimation, {
        toValue: 1,
        duration: 2000, // Duration for one complete rotation
        easing: Easing.linear,
        useNativeDriver: true // Optimize for performance
      })
    ).start()
  }, [rotateAnimation])

  // Interpolating the animation value to map to degrees (0-360)
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
        accuracy: Location.Accuracy.High // Ensure high accuracy
      })

      setCurrentLocation({
        latitude: location.coords.latitude,
        longitude: location.coords.longitude
      })

      setLoading(false) // Stop loading when location is fetched

      // Watch position changes in real-time
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

  const fetchPlaceName = async (latitude, longitude) => {
    try {
      const response = await axios.get(
        `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${GOOGLE_MAPS_APIKEY}`
      )

      if (response.data.results.length > 0) {
        const address = response.data.results?.[0]?.formatted_address
        setPlaceName(address) // Set the place name from the response
      } else {
        setPlaceName('Unknown Location')
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to fetch place name')
      console.error('Geocoding Error: ', error)
    }
  }

  const handleMapPress = (event) => {
    const { latitude, longitude } = event.nativeEvent.coordinate
    setSelectedLocation({ latitude, longitude }) // Set pinned location
    fetchPlaceName(latitude, longitude) // Fetch place name
  }

  // Render the bottom sheet content
  const renderContent = () => (
    <View style={styles.bottomSheetContainer}>
      <Text style={styles.title}>For Verification</Text>

      {/* Location Input */}
      <TouchableOpacity
        onPress={() => setMapVisible(true)}
        style={styles.input}
      >
        <Text>{!!selectedLocation ? placeName : 'Location'}</Text>
      </TouchableOpacity>

      {/* Nearest Landmark */}
      <TextInput
        style={styles.input}
        placeholder="Nearest Landmark"
        value={nearestLandmark}
        onChangeText={setNearestLandmark}
      />

      {/* Cause of Fire */}
      <TouchableOpacity
        onPress={() => setCauseModalVisible(true)}
        style={styles.input}
      >
        <Text>{causeOfFire}</Text>
      </TouchableOpacity>

      {/* Buttons */}
      <View style={styles.buttonContainer}>
        <TouchableOpacity style={styles.button}>
          <Text style={styles.buttonText}>Attach Photo</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.button}>
          <Text style={styles.buttonText}>Send</Text>
        </TouchableOpacity>
      </View>
    </View>
  )

  // Modal for map selection
  const renderMapModal = () => (
    <Modal visible={mapVisible} transparent={false} animationType="slide">
      <View style={styles.modalContainer}>
        <MapView
          style={styles.map}
          initialRegion={{
            latitude: currentLocation?.latitude || 14.5995,
            longitude: currentLocation?.longitude || 120.9842,
            latitudeDelta: 0.05,
            longitudeDelta: 0.05
          }}
          onPress={handleMapPress}
        >
          {selectedLocation && (
            <Marker coordinate={selectedLocation} title="Selected Location" />
          )}
        </MapView>
        <Button title="Confirm Location" onPress={() => setMapVisible(false)} />
      </View>
    </Modal>
  )

  // Modal for selecting the cause of fire
  const renderCauseModal = () => (
    <Modal visible={causeModalVisible} transparent={true} animationType="fade">
      <View style={styles.causeModalContainer}>
        <Text style={styles.modalTitle}>Select Cause of Fire</Text>
        <TouchableOpacity
          onPress={() => {
            setCauseOfFire('Electrical')
            setCauseModalVisible(false)
          }}
        >
          <Text style={styles.modalOption}>Electrical</Text>
        </TouchableOpacity>
        <TouchableOpacity
          onPress={() => {
            setCauseOfFire('Natural Gas')
            setCauseModalVisible(false)
          }}
        >
          <Text style={styles.modalOption}>Natural Gas</Text>
        </TouchableOpacity>
        <TouchableOpacity
          onPress={() => {
            setCauseOfFire('Unknown')
            setCauseModalVisible(false)
          }}
        >
          <Text style={styles.modalOption}>Unknown</Text>
        </TouchableOpacity>
      </View>
    </Modal>
  )

  if (loading) {
    return (
      <View style={styles.loaderContainer}>
        <Animated.Image
          source={require('@/assets/images/logo.png')} // Replace with your logo path
          style={[styles.logo, { transform: [{ rotate: rotate }] }]} // Apply the rotation animation
        />
      </View>
    )
  }

  return (
    <View style={styles.container}>
      <MapView
        style={styles.map}
        initialRegion={{
          latitude: currentLocation?.latitude || 14.5995,
          longitude: currentLocation?.longitude || 120.9842,
          latitudeDelta: 0.1,
          longitudeDelta: 0.1
        }}
        showsUserLocation={false}
        loadingEnabled={true}
      >
        {/* Highlight Pasig with a Polygon */}
        <Polygon
          coordinates={pasigCoordinates}
          strokeColor="red"
          fillColor="rgba(170, 74, 68, 0.2)"
          strokeWidth={2}
        />

        {/* Marker for Pasig City */}
        <Marker
          coordinate={pasigLocation}
          title="Pasig City"
          description="Metro Manila, Philippines"
        />

        {/* Custom marker for the user's location */}
        {currentLocation && (
          <Marker coordinate={currentLocation}>
            <Image
              source={require('@/assets/images/firetruck.png')}
              style={{ width: 50, height: 50 }}
              resizeMode="contain"
            />
          </Marker>
        )}

        {/* Directions from current location to Pasig */}
        {currentLocation && (
          <MapViewDirections
            origin={currentLocation}
            destination={pasigLocation}
            apikey={GOOGLE_MAPS_APIKEY}
            strokeWidth={4}
            strokeColor="red"
            onReady={(result) => {
              console.log(`Distance: ${result.distance} km`)
              console.log(`Duration: ${result.duration} min.`)
            }}
            onError={(errorMessage) => {
              Alert.alert('Error', `Directions request failed: ${errorMessage}`)
              console.error(errorMessage)
            }}
          />
        )}
      </MapView>

      {/* Gorhom's Bottom Sheet */}
      <BottomSheet
        ref={bottomSheetRef}
        index={1} // Default index when the bottom sheet is shown
        snapPoints={snapPoints} // Snap points for how much the bottom sheet will show
        style={styles.bottomSheet}
      >
        {renderContent()}
      </BottomSheet>

      {/* Map Modal */}
      {renderMapModal()}

      {/* Cause of Fire Modal */}
      {renderCauseModal()}
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
  modalContainer: {
    flex: 1,
    justifyContent: 'center'
  },
  causeModalContainer: {
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center'
  },
  modalTitle: {
    color: '#fff',
    fontSize: 20,
    marginBottom: 20
  },
  modalOption: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 8,
    marginVertical: 10,
    width: 200,
    textAlign: 'center'
  }
})
