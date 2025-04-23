import { Stack } from "expo-router";

export default function AccountLayout() {
  return (
    <Stack>
      <Stack.Screen
        name="index" // Default screen for the account tab
        options={{
         headerShown: false
        }}
      />
      <Stack.Screen
        name="editProfile"
        options={{
            headerShown: false
        }}
      />
    </Stack>
  );
}
