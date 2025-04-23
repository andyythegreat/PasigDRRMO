// app/tabs/_layout.js

import { Tabs } from "expo-router";
import { View, TouchableOpacity, Text, StyleSheet, Image } from "react-native";
import { MaterialIcons, MaterialCommunityIcons } from "@expo/vector-icons"; // For Icons
import { useSelector } from "react-redux";

export default function Layout() {
  const isResponder = useSelector(
    (state) => state?.user?.userInfo?.isResponder
  );


  return (
    <Tabs
      screenOptions={{
        tabBarActiveTintColor: "#fff", // White color for active tab
        tabBarInactiveTintColor: "gray", // Gray for inactive tab
        tabBarStyle: {
          backgroundColor: "#002f6c", // Pasig Blue background
          height: 70, // Increase height to accommodate the larger center button
          paddingBottom: 10,
        },
        header: ({ navigation }) => <CustomHeader />, // Custom Header
      }}
      tabBar={(props) => <MyCustomTabBar {...props} />}
    >
      <Tabs.Screen
        name="home"
        options={{
          headerShown: true,
          tabBarLabel: "Home",
          tabBarIcon: ({ color, size }) => (
            <MaterialCommunityIcons
              name="home-outline"
              color={color}
              size={size}
            />
          ),
        }}
      />
      {isResponder ? (
        <Tabs.Screen
          name="dispatch"
          options={{
            tabBarLabel: "dispatch",
            tabBarIcon: ({ color, size }) => (
              <MaterialCommunityIcons
                name="phone-alert"
                color={color}
                size={size}
              />
            ),
          }}
        />
      ) : (
        <Tabs.Screen
          name="fireAlert"
          options={{
            tabBarLabel: "Fire Alert",
            tabBarIcon: ({ color, size }) => (
              <MaterialCommunityIcons
                name="fire-alert"
                color={color}
                size={size}
              />
            ),
          }}
        />
      )}
      <Tabs.Screen
        name="emergency"
        options={{
          tabBarLabel: "Emergency",
          tabBarIcon: ({ color, size }) => (
            <MaterialCommunityIcons
              name="car-emergency"
              color={color}
              size={size}
            />
          ),
        }}
      />

        <Tabs.Screen
          name="information"
          options={{
            tabBarLabel: "Information",
            tabBarIcon: ({ color, size }) => (
              <MaterialCommunityIcons
                name="information-outline"
                color={color}
                size={size}
              />
            ),
          }}
        />
      <Tabs.Screen
        name="account"
        options={{
          tabBarLabel: "Account",
          tabBarIcon: ({ color, size }) => (
            <MaterialCommunityIcons
              name="account-outline"
              color={color}
              size={size}
            />
          ),
        }}
      />
    </Tabs>
  );
}

// Custom Header Component
const CustomHeader = () => {
  const username = useSelector(
    (state) => state?.user?.userInfo?.username
  );

  return (
    <View style={styles.headerContainer}>
      {/* Logo on the left */}
      <Image
        source={require("../../assets/images/logo.png")} // Replace with the actual path to your logo image
        style={styles.logo}
      />
      {/* Greeting Text on the right */}
      <Text style={styles.greetingText}>Hi, {`${username}`}</Text>
    </View>
  );
};

function MyCustomTabBar({ state, descriptors, navigation }) {
  return (
    <View style={styles.tabContainer}>
      {state.routes.map((route, index) => {
        const { options } = descriptors[route.key];

        const isFocused = state.index === index;
        if (options?.tabBarIcon) {
          // Skip rendering the center button within the map; add it separately
          if (route.name !== "emergency") {
            return (
              <TouchableOpacity
                key={route.key}
                accessibilityRole="button"
                onPress={() => navigation.navigate(route.name)}
                style={styles.tabButton}
              >
                {/* Change color based on whether the tab is focused */}
                {options?.tabBarIcon?.({
                  color: isFocused ? "white" : "gray",
                  size: 30,
                })}
                <Text style={{ color: isFocused ? "#fff" : "gray" }}>
                  {options.tabBarLabel}
                </Text>
              </TouchableOpacity>
            );
          }

          // Render the center button separately for "emergency"
          return (
            <TouchableOpacity
              accessibilityRole="button"
              onPress={() => navigation.navigate(route.name)} // Navigate to Fire Alert or any action
              style={styles.centerButton}
            >
              <MaterialIcons
                name="emergency-share"
                color="white"
                size={50}
              />
            </TouchableOpacity>
          );
        }
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  // Header styles
  headerContainer: {
    height: 100,
    backgroundColor: "#002f6c", // Pasig Blue
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingTop: 20,
    paddingHorizontal: 15,
  },
  logo: {
    width: 60,
    height: 60,
  },
  greetingText: {
    fontSize: 25,
    color: "white",
    fontWeight: "bold",
  },
  tabContainer: {
    flexDirection: "row",
    backgroundColor: "#002f6c", // Pasig Blue background
    height: 70,
    justifyContent: "space-around",
    alignItems: "center",
    paddingBottom: 10,
  },
  tabButton: {
    flex: 1,
    alignItems: "center",
  },
  centerButton: {
    alignItems: "center",
    justifyContent: "center",
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: "#ff0000", // Red button background
    elevation: 5, // Add some shadow for the floating effect
  },
  fireIcon: {
    position: "absolute",
    bottom: 10,
  },
});
