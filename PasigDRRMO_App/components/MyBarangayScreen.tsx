import React from 'react';
import { ScrollView, View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import MapView, { Marker, Polyline } from 'react-native-maps';

export const MyBarangayScreen = ({ incidents, handleCardPress }) => {
  return (
    <ScrollView style={styles.scrollView}>
      <View style={styles.container}>
        <View style={styles.section}>
          <View style={styles.header}>
            <MaterialCommunityIcons name="fire-truck" size={40} color="#002f6c" />
            <Text style={styles.headerText}>My Barangay</Text>
          </View>
          <View style={styles.divider} />

          {/* My Barangay Items */}
          {incidents.length > 0 ? (
            incidents.map((item) => (
              <TouchableOpacity key={item.id} onPress={() => handleCardPress(item.location)}>
                <View style={styles.itemContainer}>
                  <Text style={styles.itemText}>Caller: {item.caller}</Text>
                  <Text style={styles.itemText}>Location: {item.location}</Text>
                  <Text style={styles.itemText}>Involved: {item.involve || 'N/A'}</Text>
                  <View style={styles.statusContainer}>
                    <Text style={styles.statusPill}>{item.status}</Text>
                  </View>
                </View>
              </TouchableOpacity>
            ))
          ) : (
            <Text style={styles.noIncidentsText}>No incidents in your barangay.</Text>
          )}
        </View>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  scrollView: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  container: {
    padding: 10,
  },
  section: {
    marginBottom: 20,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  headerText: {
    fontSize: 25,
    marginLeft: 20,
    fontWeight: 'bold',
    color: '#002f6c',
  },
  divider: {
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: '#ccc',
    marginBottom: 10,
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
  },
  statusContainer: {
    marginTop: 10,
    backgroundColor: '#002f6c',
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
});
