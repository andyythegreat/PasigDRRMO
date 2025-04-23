import React, { useEffect } from "react";
import { GestureHandlerRootView } from "react-native-gesture-handler";
import { DefaultTheme, Provider as PaperProvider } from "react-native-paper";
import { Provider, useSelector } from "react-redux";
import { Stack, useRouter } from "expo-router"; // Make sure this is the correct Stack from expo-router
import { PersistGate } from "redux-persist/integration/react";
import { persistor, store } from "@/stores/store";

export const unstable_settings = {
  // Ensure any route can link back to `/`
  initialRouteName: "login",
};

const lightTheme = {
  ...DefaultTheme,
  dark: false,
  colors: {
    ...DefaultTheme.colors,
    primary: "#6200ee",
    background: "#ffffff",
    text: "#000000",
    surface: "#f5f5f5",
    accent: "#03dac4",
    placeholder: "#888888",
    notification: "#ff4081",
  },
};

export default function RootLayout() {


  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <PersistGate loading={null} persistor={persistor}>
        <PaperProvider theme={lightTheme}>
          <Provider store={store}>
            <Stack
              screenOptions={{ headerShown: false }}
            >
              <Stack.Screen name="login" />
              <Stack.Screen name="signup" />
              <Stack.Screen name="(tabs)" />
            </Stack>
          </Provider>
        </PaperProvider>
      </PersistGate>
    </GestureHandlerRootView>
  );
}
