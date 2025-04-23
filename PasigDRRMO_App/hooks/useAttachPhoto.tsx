import { useState } from "react";
import { Camera } from "expo-camera";
import { Alert } from "react-native";

export const useAttachPhoto = () => {
  const [photo, setPhoto] = useState(null);
  const [hasPermission, setHasPermission] = useState(null);

  const requestCameraPermission = async () => {
    const { status } = await Camera.requestCameraPermissionsAsync();
    setHasPermission(status === "granted");
    if (status !== "granted") {
      Alert.alert("Permission Denied", "Camera access is required to take photos.");
    }
  };

  const capturePhoto = async (cameraRef) => {
    if (!cameraRef) return;
    const photoData = await cameraRef?.current?.takePictureAsync({base64: true});
    setPhoto(photoData);
  };

  const clearPhoto = () => {
    setPhoto(null);
  };

  return {
    photo,
    hasPermission,
    requestCameraPermission,
    capturePhoto,
    clearPhoto,
  };
};
