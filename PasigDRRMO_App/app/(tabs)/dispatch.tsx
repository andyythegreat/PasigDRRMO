import React, { useState, useEffect, useCallback } from 'react'
import {
  StyleSheet,
  ScrollView,
  View,
  Text,
  TouchableOpacity,
  Modal,
  ActivityIndicator,
  Alert,
  Image,
  Platform,
  Linking,
  RefreshControl
} from 'react-native'
import { MaterialCommunityIcons } from '@expo/vector-icons'
import MapView, { Marker, Polygon, Polyline } from 'react-native-maps'
import * as Location from 'expo-location'
import { pasigCoordinates } from '@/constants/PasigCoordinates'
import {
  cancelRespond,
  checkMobileRespondOngoing,
  fetchMobileRespond,
  fetchOngoing,
  fetchOngoingCompleted,
  fetchProfile,
  fetchTruck,
  mobileRespond
} from '@/api/api'
import { GOOGLE_MAPS_APIKEY } from './emergency'
import { useDispatch, useSelector } from 'react-redux'
import MapViewDirections from 'react-native-maps-directions'
import { convertHtmlToPlainText } from '@/constants/convertToString'
import { useFocusEffect } from 'expo-router'
import ArrivedScreen from '@/components/ArrivedScreen'
import { convertToPHT } from '@/utils/convertTime'
import { isToday, set } from 'date-fns'
import { Colors } from '@/constants/Colors'
import { setUserInfo } from '@/stores/userSlice'

export default function DispatchScreen() {
  const [activeTab, setActiveTab] = useState('ongoing') // Track active tab
  const [modalVisible, setModalVisible] = useState(false)
  const [selectedCoords, setSelectedCoords] = useState(null)
  const [loading, setLoading] = useState(false)
  const [ongoingIncidents, setOngoingIncidents] = useState([])
  const [resolvedIncidents, setResolvedIncidents] = useState([])
  const [currentLocation, setCurrentLocation] = useState(null)
  const [currentLocation1, setCurrentLocation1] = useState({
    latitude: 14.5598218,
    longitude: 121.0806429
  })
  const [location, setLocation] = useState(null)
  const [timeAndDistance, setTimeAndDistance] = useState(null)
  const [responders, setResponders] = useState(null)
  const [instructions, setInstructions] = useState(null)
  const [currentStepIndex, setCurrentStepIndex] = useState(0)
  const [refreshing, setRefreshing] = useState(false) // Track refresh state
  const [ongoingResponse, setOngoingResponse] = useState(null) // To store ongoing incident
  const [selectedOnGoing, setSelectedOnGoing] = useState<number>()
  const [firetruckModalVisible, setFiretruckModalVisible] = useState(false) // New state for firetruck modal
  const [firetruck, setFiretruck] = useState([])
  const [isCancelRespondModal, setIsCancelRespondModal] = useState(false)
  const dispatch = useDispatch()

  const username = useSelector((state) => state.user?.userInfo?.username)
  const userInfo = useSelector((state) => state?.user?.userInfo)

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

  const handleRespondPress = async () => {
    if (!currentLocation) {
      Alert.alert('Error', 'Current location not found.')
      return
    }

    const firetrucks = await fetchTruck(userInfo?.barangay)
    setFiretruck(firetrucks?.data?.trucks)

    setFiretruckModalVisible(true) // Open the firetruck modal
  }

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

      console.log(ongoingResponse)

      const fogr = await fetchOngoingResponse()

      if (fogr) {
        try {
          // Make sure required details are available before calling the API
          const payload = {
            ...fogr,
            latitude: location.coords.latitude,
            longitude: location.coords.longitude
          }

          console.log({payload})

          // Call the mobileRespond API
          const response = await mobileRespond(payload)

          await fetchOngoingResponse()
        } catch (error) {}
      }

      setLoading(false) // Stop loading when location is fetched

      // Watch position changes in real-time
      Location.watchPositionAsync(
        { accuracy: Location.Accuracy.High, distanceInterval: 1 },
        async (newLocation) => {
          setCurrentLocation({
            latitude: newLocation.coords.latitude,
            longitude: newLocation.coords.longitude
          })

          if (!currentLocation) {
            return
          }

          const fogr = await fetchOngoingResponse()


          if (fogr) {
            try {
              // Make sure required details are available before calling the API
              const payload = {
                ...fogr,
                latitude: newLocation.coords.latitude,
                longitude: newLocation.coords.longitude
              }


              // Call the mobileRespond API
              const response = await mobileRespond(payload)

              await fetchOngoingResponse()
            } catch (error) {}
          }
        }
      )
    })()
  }, [])

  const fetchOngoingResponse = async () => {
    setLoading(true)

    try {
      const response = await checkMobileRespondOngoing({ username })
      const ongoing = response?.data?.ongoings?.[0]
      const ongoingResponse = await fetchOngoing(ongoing?.ongoingID)
      const ongoingItem = ongoingResponse?.data?.ongoings?.[0]
      const mobileRespond = await fetchMobileRespond({
        username,
        ongoingID: ongoingItem?.id
      })
      await fetchResponders(ongoingItem?.id)

      const mobileRespondItem = mobileRespond?.data?.mobileresponds?.find(
        (respond) => respond.username === ongoing?.username
      )

      await fetchCoordinates(ongoingItem?.location)

      setOngoingResponse({ ...ongoingItem, ...mobileRespondItem } || null)
      return { ...ongoingItem, ...mobileRespondItem }
      
    } catch (error) {
      setOngoingResponse(null)
      setFiretruckModalVisible(false)
      setModalVisible(false)
      return null
      console.error('Error fetching ongoing response:', error)
    } finally {
      setLoading(false)
    }
  }

  const openMapForDirections = () => {
    const lat = selectedCoords?.latitude
    const lon = selectedCoords?.longitude

    if (!lat || !lon) {
      alert('Selected location not found')
      return
    }

    const label = 'Fire Incident' // Label for the destination

    const url =
      Platform.OS === 'ios'
        ? `maps://app?saddr=${currentLocation?.latitude},${currentLocation?.longitude}&daddr=${lat},${lon}&dirflg=d`
        : `https://www.google.com/maps/dir/?api=1&origin=${currentLocation?.latitude},${currentLocation?.longitude}&destination=${lat},${lon}&travelmode=driving`

    Linking.canOpenURL(url)
      .then((supported) => {
        if (supported) {
          Linking.openURL(url)
        } else {
          alert(
            'Cannot open the map application. Please make sure Google Maps is installed.'
          )
        }
      })
      .catch((err) =>
        console.error('An error occurred while opening the map: ', err)
      )
  }

  // Fetch ongoing and resolved incidents
  const fetchIncidents = async () => {
    setLoading(true)
    try {
      const response = await fetchOngoing()
      const ongoingIncidents = response?.data?.ongoings.filter(
        (ongoing) => ongoing.status !== 'Fire Out'
      )
      const ongoing = ongoingIncidents?.sort(
        (a, b) => new Date(b.date) - new Date(a.date)
      )

      const resolvedResponse = await fetchOngoingCompleted()
      const resolved = resolvedResponse?.data?.ongoings?.sort(
        (a, b) => new Date(b.date) - new Date(a.date)
      )

      setOngoingIncidents(ongoing)
      setResolvedIncidents(resolved)
    } catch (error) {
      console.error('Error fetching incidents:', error)
    } finally {
      setLoading(false)
    }
  }

  // Fetch ongoing and resolved incidents
  const fetchResponders = async (ongoingID) => {
    setLoading(true)
    try {
      const response = await fetchMobileRespond({ ongoingID: ongoingID })
      const filterResponders = response?.data?.mobileresponds
      setResponders(filterResponders)
    } catch (error) {
      console.error('Error fetching incidents:', error)
    } finally {
      setLoading(false)
    }
  }

  useFocusEffect(
    useCallback(() => {
      fetchIncidents()
      fetchOngoingResponse()
    }, [activeTab])
  )

  const handleCardPress = (location, id) => {
    fetchCoordinates(location)
    if (location !== null) {
      setModalVisible(true)
    }

    fetchResponders(id)
    setLocation(location)
    setSelectedOnGoing(id)
  }

  const onRefresh = useCallback(() => {
    setRefreshing(true)
    fetchIncidents().finally(() => setRefreshing(false))
  }, [])

  const fetchCoordinates = async (location) => {
    setLoading(true)
    try {
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(
          location
        )}&key=${GOOGLE_MAPS_APIKEY}`
      )
      const data = await response.json()
      if (data.status === 'OK') {
        const coords = data.results[0].geometry.location
        setSelectedCoords({
          latitude: coords.lat,
          longitude: coords.lng
        })
      } else {
        console.error('Geocoding error:', data.status)
      }
    } catch (error) {
      console.error('Error fetching coordinates:', error)
    } finally {
      setLoading(false)
    }
  }

  if (
    ongoingResponse &&
    (ongoingResponse?.respondStatus === 'Arrived' ||
      ongoingResponse?.respondStatus === 'Request for Fire Out') &&
    ongoingResponse?.status !== 'Fire Out'
  ) {
    return (
      <ArrivedScreen
        ongoingResponse={ongoingResponse}
        selectedCoords={selectedCoords}
        fetchOngoingResponse={fetchOngoingResponse}
      />
    )
  }

  if (ongoingResponse && ongoingResponse.respondStatus === 'Arriving') {
    // Display this screen if there's an ongoing incident

    return (
      <>
        <View style={styles.container}>
          <View style={styles.container}>
            <View style={styles.mapContainer}>
              <MapView
                style={styles.map}
                initialRegion={{
                  latitude: 14.5598218,
                  longitude: 121.0806429,
                  latitudeDelta: 0.05,
                  longitudeDelta: 0.05
                }}
              >
                {/* Marker for Current Location */}
                <Marker
                  // coordinate={{ latitude: 14.5598218, longitude: 121.0806429 }}
                  coordinate={currentLocation}
                >
                  <Image
                    source={require('@/assets/images/firetruck.png')}
                    style={{ width: 50, height: 50 }}
                    resizeMode="contain"
                  />
                </Marker>

                {/* {responders?.map((responder) => {
                  return (
                    <Marker
                      key={responder.username}
                      coordinate={{
                        latitude: responder.latitude,
                        longitude: responder.longitude
                      }}
                      title={responder.username}
                      description="Responder"
                    >
                      <Image
                        source={require('@/assets/images/firetruck.png')}
                        style={{ width: 50, height: 50 }}
                        resizeMode="contain"
                      />
                    </Marker>
                  )
                })} */}

                {/* Highlight Pasig with a Polygon */}
                <Polygon
                  coordinates={pasigCoordinates}
                  strokeColor="red"
                  fillColor="rgba(170, 74, 68, 0.2)"
                  strokeWidth={2}
                />

                {/* Marker for Fire Location */}
                <Marker
                  coordinate={selectedCoords}
                  title="Fire Alert"
                  description="Fire happening here"
                >
                  <MaterialCommunityIcons name="fire" size={40} color="red" />
                </Marker>

                {/* Directions from Current Location to Fire Location */}
                <MapViewDirections
                  // origin={}
                  origin={currentLocation}
                  destination={selectedCoords}
                  apikey={GOOGLE_MAPS_APIKEY}
                  strokeWidth={4}
                  strokeColor="red"
                  onReady={(result) => {
                    setInstructions(result.legs[0].steps)
                    setTimeAndDistance({
                      distance: result.distance,
                      duration: result.duration
                    })
                  }}
                  onError={(errorMessage) => {
                    Alert.alert(
                      'Error',
                      `Directions request failed: ${errorMessage}`
                    )
                    console.error(errorMessage)
                  }}
                />
              </MapView>
            </View>

            {/* Timeline Section */}
            <ScrollView contentContainerStyle={styles.timelineContainer}>
              <Text style={styles.timelineTitle}>Fire Alert Detail</Text>

              <View style={styles.timelineItem}>
                <View style={styles.timelineDot} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Location</Text>
                  <Text style={styles.timelineDescription}>
                    {ongoingResponse?.location}
                  </Text>
                </View>
              </View>

              <View style={styles.timelineItem}>
                <View style={styles.timelineDot} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Distance</Text>
                  <Text style={styles.timelineDescription}>
                    {timeAndDistance?.distance.toFixed(2)} km
                  </Text>
                </View>
              </View>

              <View style={styles.timelineItem}>
                <View style={styles.timelineDot} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Duration</Text>
                  <Text style={styles.timelineDescription}>
                    {timeAndDistance?.duration.toFixed(2)} min.
                  </Text>
                </View>
              </View>

              <View style={styles.timelineItem}>
                <View
                  style={[styles.timelineDot, { backgroundColor: '#ccc' }]}
                />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Directions</Text>

                  {instructions?.length > 0 ? (
                    instructions?.map((step, index) => (
                      <View key={index} style={styles.instructionItem}>
                        <Text style={styles.instructionText}>
                          {index + 1}.{' '}
                          {convertHtmlToPlainText(step.html_instructions)}
                        </Text>
                        <Text style={styles.instructionDetails}>
                          Distance: {step.distance.text}, Duration:{' '}
                          {step.duration.text}
                        </Text>
                      </View>
                    ))
                  ) : (
                    <Text style={styles.noInstructionsText}>
                      No instructions available yet.
                    </Text>
                  )}
                </View>
              </View>
            </ScrollView>

            <TouchableOpacity
              style={styles.directionButton}
              onPress={openMapForDirections}
            >
              <Text style={styles.directionButtonText}>Get Directions</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.directionButton, { backgroundColor: 'red' }]}
              onPress={() => {
                setIsCancelRespondModal(true)
              }}
            >
              <Text style={styles.directionButtonText}>Cancel Respond</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[
                styles.respondButton
                // Number(timeAndDistance?.distance) > 0.5 ? { opacity: 0.5 } : {}
              ]}
              // disabled={Number(timeAndDistance?.distance) > 0.5}
              onPress={async () => {
                if (!currentLocation) {
                  Alert.alert('Error', 'Current location not found.')
                  return
                }

                try {
                  const payload = {
                    username,
                    location: ongoingResponse?.location ?? '',
                    longitude: currentLocation.longitude,
                    latitude: currentLocation.latitude,
                    timeRespond: new Date(
                      ongoingResponse?.timeRespond
                    ).toISOString(),
                    respondStatus: 'Arrived',
                    timeArrived: new Date().toISOString(),
                    ongoingID: ongoingResponse?.ongoingID as number,
                    truckID: ongoingResponse.truckID
                  }

                  // Call the mobileRespond API
                  const response = await mobileRespond(payload)

                  await fetchOngoingResponse()
                } catch (error) {
                  console.error('Error responding:', error)
                  Alert.alert('Error', 'Failed to send response.')
                }
              }}
            >
              <Text style={styles.respondButtonText}>Arrived</Text>
            </TouchableOpacity>
          </View>
        </View>
        <Modal
          visible={isCancelRespondModal}
          transparent={true}
          animationType="slide"
          onRequestClose={() => {
            setIsCancelRespondModal(!isCancelRespondModal)
          }}
        >
          <View style={styles.cancelModalContainer}>
            <View style={styles.cancelModalContent}>
              <TouchableOpacity
                style={styles.cancelCloseButton}
                onPress={() => {
                  setIsCancelRespondModal(!isCancelRespondModal)
                }}
              >
                <Text style={styles.cancelCoseButtonText}>X</Text>
              </TouchableOpacity>
              <MaterialCommunityIcons
                name="information-outline"
                size={40}
                color="#1E3A8A"
                style={{ textAlign: 'center' }}
              />
              <Text
                style={{
                  fontSize: 20,
                  textAlign: 'center',
                  marginVertical: 20
                }}
              >
                Are you sure you want to cancel respond?
              </Text>
              <View style={{ flexDirection: 'row', gap: 10 }}>
                <TouchableOpacity
                  style={[
                    styles.sendButton,
                    { backgroundColor: 'gray', flex: 0.5 }
                  ]}
                  onPress={() => {
                    setIsCancelRespondModal(!isCancelRespondModal)
                  }}
                >
                  <Text style={styles.sendButtonText}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[
                    styles.sendButton,
                    {
                      flex: 0.5
                    }
                  ]}
                  onPress={async () => {
                    const response = await cancelRespond({
                      username: userInfo?.username,
                      ongoingID: ongoingResponse?.id
                    })

                    setIsCancelRespondModal(false)
                    setFiretruckModalVisible(false)
                    setModalVisible(false)

                    await fetchOngoingResponse()
                  }}
                >
                  <Text style={styles.sendButtonText}>Yes</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </Modal>
      </>
    )
  }

  return (
    <View style={styles.container}>
      {/* Tabs for Ongoing & Resolved */}
      <View style={styles.tabs}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'ongoing' && styles.activeTab]}
          onPress={() => setActiveTab('ongoing')}
        >
          <Text style={styles.tabText}>Ongoing</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'resolved' && styles.activeTab]}
          onPress={() => setActiveTab('resolved')}
        >
          <Text style={styles.tabText}>Resolved</Text>
        </TouchableOpacity>
      </View>

      {/* Incidents List */}
      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        } // Add refresh control for pull-to-refresh
      >
        {loading && (
          <View style={styles.centerContent}>
            <ActivityIndicator size="large" color={'#002f6c'} />
          </View>
        )}
        {!loading &&
          activeTab === 'ongoing' &&
          (ongoingIncidents?.length > 0 ? (
            ongoingIncidents.map((item) => (
              <TouchableOpacity
                key={item.id}
                onPress={() => {
                  handleCardPress(item.location, item.id)
                }}
              >
                <View style={styles.itemContainer}>
                  <Text style={styles.itemText}>Caller: {item.caller}</Text>
                  <Text style={styles.itemText}>Location: {item.location}</Text>
                  <Text style={styles.itemText}>
                    Involved: {item.involve || 'N/A'}
                  </Text>
                  <View
                    style={[
                      styles.statusContainer,
                      { backgroundColor: getStatusColor(item.status) }
                    ]}
                  >
                    <Text style={styles.statusPill}>{item.status}</Text>
                  </View>
                </View>
              </TouchableOpacity>
            ))
          ) : (
            <View style={styles.centerContent}>
              <Text style={styles.noIncidentsText}>No ongoing incidents</Text>
            </View>
          ))}

        {!loading &&
          activeTab === 'resolved' &&
          (resolvedIncidents?.length > 0 ? (
            resolvedIncidents?.map((item) => (
              <TouchableOpacity key={item.id}>
                <View style={styles.itemContainer}>
                  <Text style={styles.itemText}>Caller: {item.caller}</Text>
                  <Text style={styles.itemText}>Location: {item.location}</Text>
                  <Text style={styles.itemText}>
                    Involved: {item.involve || 'N/A'}
                  </Text>
                  <View
                    style={[
                      styles.statusContainer,
                      { backgroundColor: getStatusColor(item.status) }
                    ]}
                  >
                    <Text style={styles.statusPill}>{item.status}</Text>
                  </View>
                </View>
              </TouchableOpacity>
            ))
          ) : (
            <View style={styles.centerContent}>
              <Text style={styles.noIncidentsText}>No resolved incidents</Text>
            </View>
          ))}
      </ScrollView>

      {/* Modal for the Map */}
      {selectedCoords && currentLocation && (
        <Modal
          visible={modalVisible && !firetruckModalVisible}
          animationType="slide"
        >
          <View style={styles.container}>
            <View style={styles.mapContainer}>
              <MapView
                style={styles.map}
                initialRegion={{
                  latitude: 14.5598218,
                  longitude: 121.0806429,
                  latitudeDelta: 0.05,
                  longitudeDelta: 0.05
                }}
              >
                {/* Marker for Current Location */}
                <Marker coordinate={currentLocation}>
                  <Image
                    source={require('@/assets/images/firetruck.png')}
                    style={{ width: 50, height: 50 }}
                    resizeMode="contain"
                  />
                </Marker>

                {/* {responders?.map((responder) => {
                  return (
                    <Marker
                      key={responder.username}
                      coordinate={{
                        latitude: responder.latitude,
                        longitude: responder.longitude
                      }}
                      title={responder.username}
                      description="Responder"
                    >
                      <Image
                        source={require('@/assets/images/firetruck.png')}
                        style={{ width: 50, height: 50 }}
                        resizeMode="contain"
                      />
                    </Marker>
                  )
                })} */}

                {/* Highlight Pasig with a Polygon */}
                <Polygon
                  coordinates={pasigCoordinates}
                  strokeColor="red"
                  fillColor="rgba(170, 74, 68, 0.2)"
                  strokeWidth={2}
                />

                {/* Marker for Fire Location */}
                <Marker
                  coordinate={selectedCoords}
                  title="Fire Alert"
                  description="Fire happening here"
                >
                  <MaterialCommunityIcons name="fire" size={40} color="red" />
                </Marker>

                {/* Directions from Current Location to Fire Location */}
                <MapViewDirections
                  origin={currentLocation}
                  destination={selectedCoords}
                  apikey={GOOGLE_MAPS_APIKEY}
                  strokeWidth={4}
                  strokeColor="red"
                  onReady={(result) => {
                    setInstructions(result.legs[0].steps)
                    setTimeAndDistance({
                      distance: result.distance,
                      duration: result.duration
                    })
                  }}
                  onError={(errorMessage) => {
                    console.error(errorMessage)
                  }}
                />
              </MapView>
            </View>

            {/* Timeline Section */}
            <ScrollView contentContainerStyle={styles.timelineContainer}>
              <View
                style={{
                  flexDirection: 'row',
                  position: 'relative',
                  justifyContent: 'center',
                  alignItems: 'center'
                }}
              >
                <TouchableOpacity
                  style={styles.closeButton}
                  onPress={() => setModalVisible(false)}
                >
                  <MaterialCommunityIcons name="arrow-left" size={30} />
                </TouchableOpacity>
                <Text style={styles.timelineTitle}>Fire Alert Detail</Text>
              </View>

              <View style={styles.timelineItem}>
                <View style={styles.timelineDot} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Location</Text>
                  <Text style={styles.timelineDescription}>{location}</Text>
                </View>
              </View>

              <View style={styles.timelineItem}>
                <View style={styles.timelineDot} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Distance</Text>
                  <Text style={styles.timelineDescription}>
                    {timeAndDistance?.distance.toFixed(2)} km
                  </Text>
                </View>
              </View>

              <View style={styles.timelineItem}>
                <View style={styles.timelineDot} />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Duration</Text>
                  <Text style={styles.timelineDescription}>
                    {timeAndDistance?.duration.toFixed(2)} min.
                  </Text>
                </View>
              </View>

              <View style={styles.timelineItem}>
                <View
                  style={[styles.timelineDot, { backgroundColor: '#ccc' }]}
                />
                <View style={styles.timelineContent}>
                  <Text style={styles.timelineHeading}>Directions</Text>

                  {instructions?.length > 0 ? (
                    instructions?.map((step, index) => (
                      <View key={index} style={styles.instructionItem}>
                        <Text style={styles.instructionText}>
                          {index + 1}.{' '}
                          {convertHtmlToPlainText(step.html_instructions)}
                        </Text>
                        <Text style={styles.instructionDetails}>
                          Distance: {step.distance.text}, Duration:{' '}
                          {step.duration.text}
                        </Text>
                      </View>
                    ))
                  ) : (
                    <Text style={styles.noInstructionsText}>
                      No instructions available yet.
                    </Text>
                  )}
                </View>
              </View>
            </ScrollView>
            {timeAndDistance && (
              <TouchableOpacity
                style={styles.respondButton}
                onPress={handleRespondPress}
                // onPress={async () => {
                //   if (!currentLocation) {
                //     Alert.alert("Error", "Current location not found.");
                //     return;
                //   }

                //   try {
                //     // Make sure required details are available before calling the API
                //     const payload = {
                //       username,
                //       location: location ?? "",
                //       longitude: currentLocation.longitude,
                //       latitude: currentLocation.latitude,
                //       timeRespond: new Date().toISOString(),
                //       respondStatus: "Arriving",
                //       timeArrived: "00:00:00",
                //       ongoingID: selectedOnGoing as number,
                //     };

                //     // Call the mobileRespond API
                //     const response = await mobileRespond(payload);

                //     await fetchOngoingResponse();

                //     Alert.alert("Success", "Response sent successfully.");
                //   } catch (error) {
                //     console.error("Error responding:", error);
                //     Alert.alert("Error", "Failed to send response.");
                //   }
                // }}
              >
                <Text style={styles.respondButtonText}>Respond</Text>
              </TouchableOpacity>
            )}
          </View>
        </Modal>
      )}
      {/* Firetruck Selection Modal */}
      <Modal
        visible={firetruckModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setFiretruckModalVisible(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>
              {firetruck?.length === 0 || !firetruck
                ? 'No Firetrucks Available'
                : 'Choose Firetruck'}
            </Text>
            <TouchableOpacity
              onPress={() => setFiretruckModalVisible(false)}
              style={styles.modalCloseButton}
            >
              <Text style={styles.modalCloseButtonText}>X</Text>
            </TouchableOpacity>

            {firetruck?.length === 0 ||
              (!firetruck && (
                <Text style={{ marginBottom: 20, textAlign: 'center' }}>
                  There's no firetrucks available for your barangay.
                </Text>
              ))}
            {firetruck?.map((truck) => (
              <TouchableOpacity
                key={truck.id}
                style={styles.firetruckOption}
                onPress={async () => {
                  try {
                    const payload = {
                      username,
                      location: location ?? '',
                      longitude: currentLocation.longitude,
                      latitude: currentLocation.latitude,
                      timeRespond: new Date().toISOString(),
                      respondStatus: 'Arriving',
                      timeArrived: '00:00:00',
                      ongoingID: selectedOnGoing as number,
                      truckID: truck.id
                    }

                    // Call the mobileRespond API
                    const response = await mobileRespond(payload)

                    await fetchOngoingResponse()
                  } catch (error) {
                    console.error('Error responding:', error)
                    Alert.alert('Error', 'Failed to send response.')
                  }
                }}
              >
                <Text style={styles.firetruckText}>
                  {truck.barangay.split('_').join(' ')} - {truck.unitName}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>
      </Modal>
    </View>
  )
}

export const getStatusColor = (status) => {
  switch (status) {
    case 'Positive Alarm':
      return '#28a745'
    case 'Negative Alarm':
      return '#6c757d'
    case 'First Alarm':
      return '#ffcc00'
    case 'Second Alarm':
      return '#ff9900'
    case 'Third Alarm':
      return '#ff6600'
    case 'Fourth Alarm':
      return '#ff4500'
    case 'Fifth Alarm':
      return '#ff0000'
    case 'Fire Under Control':
      return '#f0ad4e'
    case 'Task Force Alpha':
      return '#007bff'
    case 'Task Force Bravo':
      return '#0056b3'
    case 'Task Force Charlie':
      return '#003d7a'
    case 'General Alarm':
      return '#dc3545'
    case 'Fire Out':
      return '#5cb85c'
    default:
      return '#002f6c' // Default Pasig Blue
  }
}

const styles = StyleSheet.create({
  cancelModalContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.5)'
  },
  cancelModalContent: {
    width: '90%',
    padding: 20,
    backgroundColor: '#fff',
    borderRadius: 10,
    position: 'relative'
  },
  cancelCloseButton: {
    position: 'absolute',
    right: 10,
    top: 10
  },
  cancelCloseButtonText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333'
  },
  modalContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.5)'
  },
  modalContent: {
    width: '80%',
    backgroundColor: 'white',
    borderRadius: 10,
    padding: 20,
    alignItems: 'center'
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 20
  },
  modalCloseButton: {
    position: 'absolute',
    top: 10,
    right: 10,
    padding: 5
  },
  modalCloseButtonText: {
    fontSize: 18,
    fontWeight: 'bold'
  },
  firetruckOption: {
    backgroundColor: '#002f6c',
    padding: 15,
    borderRadius: 5,
    marginVertical: 10,
    width: '100%',
    alignItems: 'center'
  },
  firetruckText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold'
  },
  respondButton: {
    backgroundColor: '#002f6c',
    padding: 16,
    borderRadius: 10,
    margin: 16,
    alignItems: 'center'
  },
  respondButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold'
  },
  centerContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center'
  },
  instructionsContainer: {
    padding: 16,
    flexGrow: 1
  },
  instructionsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10
  },
  instructionItem: {
    marginBottom: 10,
    marginTop: 10,
    padding: 10,
    backgroundColor: '#f0f0f0',
    borderRadius: 8
  },
  instructionText: {
    fontSize: 14,
    color: '#333'
  },
  instructionDetails: {
    fontSize: 12,
    color: '#888'
  },
  noInstructionsText: {
    fontSize: 14,
    color: '#888',
    textAlign: 'center',
    marginTop: 20
  },
  tabs: {
    flexDirection: 'row',
    justifyContent: 'center',
    paddingVertical: 10
  },
  tab: {
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderBottomWidth: 3,
    borderBottomColor: 'transparent'
  },
  activeTab: {
    borderBottomColor: '#002f6c'
  },
  tabText: {
    fontSize: 16,
    color: '#002f6c',
    fontWeight: 'bold'
  },
  itemContainer: {
    backgroundColor: '#e0f0ff',
    padding: 15,
    borderRadius: 10,
    marginHorizontal: 10,
    marginVertical: 5
  },
  itemText: {
    fontSize: 16,
    color: '#333',
    marginBottom: 5
  },
  statusContainer: {
    marginTop: 10,
    alignSelf: 'flex-start',
    paddingVertical: 5,
    paddingHorizontal: 15,
    borderRadius: 20
  },
  statusPill: {
    fontSize: 14,
    color: '#fff'
  },

  map: {
    flex: 1
  },

  container: {
    flex: 1,
    backgroundColor: '#fff'
  },
  mapContainer: {
    height: '40%',
    backgroundColor: '#f5f5f5'
  },

  timelineContainer: {
    padding: 16,
    backgroundColor: '#fff',
    flexGrow: 1
  },
  timelineTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    textAlign: 'center',
    alignSelf: 'center',
    marginBottom: 16
  },
  timelineItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 16
  },
  timelineDot: {
    width: 10,
    height: 10,
    backgroundColor: '#002f6c',
    borderRadius: 5,
    marginTop: 6,
    marginRight: 16
  },
  timelineContent: {
    flex: 1
  },
  timelineHeading: {
    fontSize: 16,
    fontWeight: 'bold',
    padding: 5
  },
  timelineTime: {
    fontSize: 12,
    color: '#888',
    marginBottom: 4
  },
  timelineDescription: {
    fontSize: 14,
    color: '#555'
  },
  respondButton: {
    backgroundColor: '#002f6c',
    padding: 16,
    borderRadius: 10,
    margin: 16,
    alignItems: 'center'
  },
  respondButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold'
  },
  closeButton: {
    padding: 10,
    marginBottom: 10,
    position: 'absolute',
    left: -10,
    top: -10
  },
  closeButtonText: {
    color: '#000',
    fontSize: 16
  },
  directionButton: {
    backgroundColor: 'green',
    padding: 16,
    borderRadius: 10,
    marginTop: 16,
    marginHorizontal: 16,
    alignItems: 'center'
  },
  directionButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold'
  },
  ongoingTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    textAlign: 'center',
    marginTop: 20,
    color: '#002f6c' // Pasig Blue
  },
  ongoingDetails: {
    marginTop: 20,
    padding: 20,
    backgroundColor: '#e0f0ff', // Light Blue background for details
    borderRadius: 10,
    alignItems: 'center'
  },
  ongoingText: {
    fontSize: 18,
    color: '#333',
    marginBottom: 10
  },
  sendButton: {
    backgroundColor: '#1E3A8A',
    paddingVertical: 10,
    borderRadius: 5,
    alignItems: 'center'
  },
  sendButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 15
  }
})
