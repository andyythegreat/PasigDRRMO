import React, { useState } from 'react';
import { StyleSheet, ScrollView, View, Text, TouchableOpacity, Linking, Platform } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native'; // Import useFocusEffect
import { fetchContact } from '@/api/api';

// Assuming fetchContact is a function that fetches the hotlines

export default function InfoScreen() {
  const [hotlines, setHotlines] = useState([]);

  // Fetch hotlines when the screen is focused
  useFocusEffect(
    React.useCallback(() => {
      const loadHotlines = async () => {
        try {
          const fetchedHotlines = await fetchContact();
          setHotlines(fetchedHotlines?.data?.contacts || []);
        } catch (error) {
          console.error('Failed to fetch hotlines', error);
        }
      };

      loadHotlines();
    }, [])
  );

  // Function to handle making a phone call
  const handlePhonePress = (phoneNumber) => {
    const formattedPhoneNumber = `tel:${phoneNumber.replace(/[^0-9]/g, '')}`;
    Linking.openURL(formattedPhoneNumber).catch(err =>
      console.error('Failed to make a phone call', err)
    );
  };

  return (
    <ScrollView style={styles.container}>
      {/* Title */}
      <View style={styles.titleContainer}>
        <MaterialCommunityIcons name="phone" size={28} color="#333" />
        <Text style={styles.title}>Emergency Hotlines</Text>
      </View>

      {/* Hotlines List */}
      {hotlines.map((hotline, index) => (
        <TouchableOpacity
          key={index}
          style={styles.hotlineContainer}
          onPress={() => handlePhonePress(hotline.contact_number)}
        >
          <View style={styles.hotlineContent}>
            <Text style={styles.hotlineName}>{hotline.contact_name}</Text>
            <View style={styles.phoneRow}>
              <MaterialCommunityIcons name="phone" size={20} color="#007AFF" />
              <Text style={styles.phoneNumber}>{hotline.contact_number}</Text>
            </View>
          </View>
        </TouchableOpacity>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 20,
  },
  titleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#ddd',
  },
  title: {
    fontSize: 24,
    fontWeight: '600',
    marginLeft: 10,
    color: '#333',
  },
  hotlineContainer: {
    paddingVertical: 15,
    paddingHorizontal: 10,
    backgroundColor: '#e0f0ff',
    borderRadius: 8,
    marginBottom: 15,
    elevation: Platform.OS === 'android' ? 3 : 0,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
  },
  hotlineContent: {
    flexDirection: 'column',
    justifyContent: 'space-between',
  },
  hotlineName: {
    fontSize: 16,
    fontWeight: '500',
    color: '#333',
    marginBottom: 8,
  },
  phoneRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  phoneNumber: {
    fontSize: 16,
    marginLeft: 10,
    color: '#007AFF',
    textDecorationLine: 'underline',
  },
});
