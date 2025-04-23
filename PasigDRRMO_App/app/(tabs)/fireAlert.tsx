import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  ScrollView,
  View,
  Text,
  TouchableOpacity,
  Modal,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import MapView, { Marker, Polyline } from 'react-native-maps';
import { pasigCoordinates } from '@/constants/PasigCoordinates';
import { GOOGLE_MAPS_APIKEY } from './emergency';
import { useSelector } from 'react-redux';
import { fetchOngoing } from '@/api/api';

const getStatusColor = (status) => {
  switch (status) {
    case 'Positive Alarm':
      return '#28a745';
    case 'Negative Alarm':
      return '#6c757d';
    case 'First Alarm':
      return '#ffcc00';
    case 'Second Alarm':
      return '#ff9900';
    case 'Third Alarm':
      return '#ff6600';
    case 'Fourth Alarm':
      return '#ff4500';
    case 'Fifth Alarm':
      return '#ff0000';
    case 'Fire Under Control':
      return '#f0ad4e';
    case 'Task Force Alpha':
      return '#007bff';
    case 'Task Force Bravo':
      return '#0056b3';
    case 'Task Force Charlie':
      return '#003d7a';
    case 'General Alarm':
      return '#dc3545';
    case 'Fire Out':
      return '#5cb85c';
    default:
      return '#002f6c';
  }
};

export default function FireAlert() {
  const [modalVisible, setModalVisible] = useState(false);
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false); // New refreshing state
  const [myBarangayIncidents, setMyBarangayIncidents] = useState([]);
  const [otherBarangayIncidents, setOtherBarangayIncidents] = useState([]);
  const [activeTab, setActiveTab] = useState('myBarangay');

  const userBarangay = useSelector((state) => state.user?.userInfo?.barangay);

  const fetchOngoingIncidents = async () => {
    setLoading(true);
    try {
      const response = await fetchOngoing();
      const ongoingIncidents = response?.data?.ongoings;

      const myBarangayData = ongoingIncidents
        .filter((incident) => incident.barangay?.toLowerCase() === userBarangay.toLowerCase())
        .sort((a, b) => new Date(b.date) - new Date(a.date));

      const otherBarangayData = ongoingIncidents
        .filter((incident) => incident.barangay?.toLowerCase() !== userBarangay.toLowerCase())
        .sort((a, b) => new Date(b.date) - new Date(a.date));


      setMyBarangayIncidents(myBarangayData);
      setOtherBarangayIncidents(otherBarangayData);
    } catch (error) {
      console.error('Error fetching ongoing incidents:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOngoingIncidents();
  }, []);


  // Refresh handler to re-fetch data
  const onRefresh = async () => {
    setRefreshing(true);
    await fetchOngoingIncidents();
    setRefreshing(false);
  };

  const handleCardPress = (location) => {
    fetchCoordinates(location);
  };

  const fetchCoordinates = async (location) => {
    setLoading(true);
    try {
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(location)}&key=${GOOGLE_MAPS_APIKEY}`
      );
      const data = await response.json();
      if (data.status === 'OK') {
        const coords = data.results[0].geometry.location;
        setSelectedLocation({
          latitude: coords.lat,
          longitude: coords.lng,
        });
        setModalVisible(true);
      } else {
        console.error('Geocoding error:', data.status);
      }
    } catch (error) {
      console.error('Error fetching coordinates:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderIncidents = () => {
    const incidents = activeTab === 'myBarangay' ? myBarangayIncidents : otherBarangayIncidents;


    // Get today's date in 'YYYY-MM-DD' format
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
  
    // Filter incidents that have today's date
    const noIncidentsMessage = activeTab === 'myBarangay'
      ? 'No incidents in your barangay.'
      : 'No incidents in other barangays.';

    return incidents.length > 0 ? (
      incidents.map((item) => (
        <TouchableOpacity key={item.id} onPress={() => handleCardPress(item.location)}>
          <View style={styles.itemContainer}>
            <Text style={styles.itemText}>Date: {item.date}</Text>
            <Text style={styles.itemText}>Caller: {item.caller}</Text>
            <Text style={styles.itemText}>Location: {item.location}</Text>
            <Text style={styles.itemText}>Involved: {item.involve || 'N/A'}</Text>
            <View style={[styles.statusContainer, { backgroundColor: getStatusColor(item.status) }]}>
              <Text style={styles.statusPill}>{item.status}</Text>
            </View>
          </View>
        </TouchableOpacity>
      ))
    ) : (
      <Text style={styles.noIncidentsText}>{noIncidentsMessage}</Text>
    );
  };

  return (
    <View style={styles.container}>
      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[styles.tabButton, activeTab === 'myBarangay' ? styles.activeTab : null]}
          onPress={() => setActiveTab('myBarangay')}
        >
          <Text style={styles.tabText}>My Barangay</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tabButton, activeTab === 'otherBarangay' ? styles.activeTab : null]}
          onPress={() => setActiveTab('otherBarangay')}
        >
          <Text style={styles.tabText}>Other Barangays</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} /> // Adding the RefreshControl
        }
      >
        {renderIncidents()}
      </ScrollView>

      {selectedLocation && (
        <Modal visible={modalVisible} animationType="slide">
          <View style={styles.mapContainer}>
            <MapView
              style={styles.map}
              initialRegion={{
                latitude: selectedLocation.latitude,
                longitude: selectedLocation.longitude,
                latitudeDelta: 0.05,
                longitudeDelta: 0.05,
              }}
            >
              <Marker coordinate={selectedLocation} title="Fire Alert" description="Fire happening here">
                <MaterialCommunityIcons name="fire" size={40} color="red" />
              </Marker>

              <Polyline
                coordinates={pasigCoordinates}
                strokeColor="red"
                fillColor="rgba(170, 74, 68, 0.2)"
                strokeWidth={2}
              />
            </MapView>

            <TouchableOpacity style={styles.closeButton} onPress={() => setModalVisible(false)}>
              <Text style={styles.closeButtonText}>Close Map</Text>
            </TouchableOpacity>
          </View>
        </Modal>
      )}

      {loading && <ActivityIndicator size="large" color="#002f6c" style={styles.loader} />}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  tabContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: '#f0f0f0',
    paddingVertical: 10,
  },
  tabButton: {
    paddingVertical: 10,
    paddingHorizontal: 20,
  },
  activeTab: {
    borderBottomWidth: 2,
    borderBottomColor: '#002f6c',
  },
  tabText: {
    fontSize: 16,
    color: '#002f6c',
    fontWeight: 'bold',
  },
  itemContainer: {
    backgroundColor: '#e0f0ff',
    padding: 15,
    borderRadius: 8,
    marginBottom: 10,
    elevation: 3,
  },
  itemText: {
    fontSize: 16,
    color: '#333',
    marginBottom: 5,
  },
  statusContainer: {
    marginTop: 10,
    alignSelf: 'flex-start',
    paddingVertical: 5,
    paddingHorizontal: 15,
    borderRadius: 20,
  },
  statusPill: {
    fontSize: 14,
    color: '#fff',
  },
  noIncidentsText: {
    fontSize: 16,
    color: '#888',
    textAlign: 'center',
    marginTop: 10,
  },
  mapContainer: {
    flex: 1,
  },
  map: {
    flex: 1,
  },
  closeButton: {
    backgroundColor: '#002f6c',
    padding: 10,
    alignItems: 'center',
  },
  closeButtonText: {
    color: '#fff',
    fontSize: 18,
  },
  loader: {
    position: 'absolute',
    top: '50%',
    left: '50%',
  },
});
