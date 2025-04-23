import React, { useEffect } from 'react';
import { StyleSheet, ScrollView, View, Text, TouchableOpacity, Linking, Platform, Alert } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

export default function InfoScreen({ navigation }) {
  useEffect(() => {
    Alert.alert('Incoming Feature', 'This feature is not yet available');
    navigation.goBack()
  } , []);

  const hotlines = [
    { name: 'PASIG CITY DRRMO EMERGENCY HOTLINE', number: '8643 - 0000' },
    { name: 'BUREAU OF FIRE PROTECTION', number: '8641 - 2815' },
    { name: 'PASIG CITY COMMAND CENTER', number: '8643 - 0000' },
    { name: 'PHILIPPINE NATIONAL POLICE', number: '8477 - 7953' },
    { name: 'PASIG CITY CHILDREN’S HOSPITAL', number: '8643 - 2222' },
    { name: 'PASIG CITY GENERAL HOSPITAL', number: '8643 - 3333' },
    { name: 'PASIG CITY GENERAL HOSPITAL', number: '8642 - 7379' },
    { name: 'PASIG CITY GENERAL HOSPITAL', number: '8643 - 7381' },
  ];

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
          onPress={() => handlePhonePress(hotline.number)}
        >
          <View style={styles.hotlineContent}>
            <Text style={styles.hotlineName}>{hotline.name}</Text>
            <View style={styles.phoneRow}>
              <MaterialCommunityIcons name="phone" size={20} color="#007AFF" />
              <Text style={styles.phoneNumber}>{hotline.number}</Text>
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
    elevation: Platform.OS === 'android' ? 3 : 0, // Android shadow
    shadowColor: '#000', // iOS shadow
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
