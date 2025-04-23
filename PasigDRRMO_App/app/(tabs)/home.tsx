  import React, { useEffect, useState } from 'react';
  import { Image, StyleSheet, View, TouchableOpacity, Modal, Pressable } from 'react-native';
  import { HelloWave } from '@/components/HelloWave';
  import ParallaxScrollView from '@/components/ParallaxScrollView';
  import { ThemedText } from '@/components/ThemedText';
  import { ThemedView } from '@/components/ThemedView';
  import { Avatar, Card, Text } from 'react-native-paper';
  import { Entypo, MaterialCommunityIcons } from '@expo/vector-icons';
  import { fetchAnnouncements } from '@/api/api';

  const AnnouncementCard = ({ date, title, content, imageUrl }) => {
    const [showFullText, setShowFullText] = useState(false);
    const [modalVisible, setModalVisible] = useState(false); // State to control modal visibility
  
    return (
      <>
        <Card style={styles.card}>
          <Card.Content style={{ paddingBottom: 10 }}>
            <Text variant="labelSmall">Date: {date}</Text>
            <Text variant="titleLarge" style={styles.cardTitle}>{title}</Text>
  
            {/* Toggle between truncated and full content */}
            <Text variant="bodyMedium" style={{fontSize: 16,textAlign: 'justify'}} numberOfLines={showFullText ? undefined : 3}>
              {content}
            </Text>
  
            {/* Show "See More" or "See Less" button if the content is too long */}
            {content.length > 150 && (
              <TouchableOpacity onPress={() => setShowFullText(!showFullText)}>
                <Text style={styles.seeMoreText}>{showFullText ? 'See Less' : 'See More'}</Text>
              </TouchableOpacity>
            )}
          </Card.Content>
  
          {/* Pressing on image will open the modal */}
          <TouchableOpacity onPress={() => setModalVisible(true)}>
            <Card.Cover style={{ borderRadius: 0 }} source={{ uri: imageUrl }} />
          </TouchableOpacity>
        </Card>
  
        {/* Full-screen modal to display the image */}
        <Modal
          visible={modalVisible}
          transparent={true}
          animationType="fade"
          onRequestClose={() => setModalVisible(false)} // Close modal on request
        >
          <Pressable style={styles.modalContainer}onPress={() => setModalVisible(false)}>
            <TouchableOpacity style={styles.modalOverlay} onPress={() => setModalVisible(false)} />
            <Image source={{ uri: imageUrl }} style={styles.fullScreenImage} resizeMode="contain" />
          </Pressable>
        </Modal>
      </>
    );
  };
  

    
  
  export default function HomeScreen() {
    const [announcements, setAnnouncements] = useState([]); // State to hold announcements
    const [loading, setLoading] = useState(true); // Loading state
  
    // Fetch announcements when the component mounts
    useEffect(() => {
      const loadAnnouncements = async () => {
        try {
          const response = await fetchAnnouncements(); // Call the API


          const fetchedAnnouncements = response.data?.announcements; // Extract the data from the response
          setAnnouncements(fetchedAnnouncements); // Set the announcements data
        } catch (error) {
          console.error('Failed to fetch announcements:', error); // Error handling
        } finally {
          setLoading(false); // Stop loading once the data is fetched
        }
      };
  
      loadAnnouncements();
    }, []);
  
    // Display a loading indicator while fetching the announcements
    if (loading) {
      return (
        <View style={styles.loadingContainer}>
          <Text>Loading...</Text>
        </View>
      );
    }
  
    return (
      <ParallaxScrollView
        headerBackgroundColor={{ light: '#A1CEDC', dark: '#1D3D47' }}
        headerImage={
          <Entypo
            name="megaphone"
            size={300}
            color={'#002f6c'}
            style={styles.reactLogo}
          />
        }
      >
        <View style={styles.container}>
          <ThemedView style={styles.header}>
            <ThemedText type="title">Announcement! </ThemedText>
            <HelloWave emoji="📣" />
          </ThemedView>
  
          {/* Render fetched announcements dynamically */}
          {announcements.map((announcement) => (
            <AnnouncementCard
              key={announcement.id}
              date={announcement.date}
              title={announcement.subject}
              content={announcement.message}
              imageUrl={announcement.photo || 'https://picsum.photos/700'} // Fallback image URL if none provided
            />
          ))}
        </View>
      </ParallaxScrollView>
    );
  }
  

  const styles = StyleSheet.create({
    container: {
      flex: 1,
    },
    loadingContainer: {
      flex: 1,
      justifyContent: 'center',
      alignItems: 'center',
    },
    header: {
      flexDirection: 'row',
      alignItems: 'center',
      marginBottom: 20,
    },
    reactLogo: {
      height: 230,
      width: 300,
      position: 'absolute',
      bottom: 0,
      left: 0,
    },
    card: {
      backgroundColor: 'lightblue',
      marginVertical: 10,
      borderRadius: 0,
    },
    cardTitle: {
      fontFamily: 'Times New Roman',
      fontWeight: 'bold',
      fontSize: 25
    },
    seeMoreText: {
      color: '#1D3D47', // Style for "See More" and "See Less" button
      fontWeight: 'bold',
      marginTop: 10,
    },
    modalContainer: {
      flex: 1,
      backgroundColor: 'rgba(0, 0, 0, 0.9)', // Dark overlay background
      justifyContent: 'center',
      alignItems: 'center',
    },
    modalOverlay: {
      position: 'absolute',
      top: 0,
      bottom: 0,
      left: 0,
      right: 0,
    },
    fullScreenImage: {
      width: '100%',
      height: '100%',
    },
  });
  
