import React, { useCallback, useEffect, useState } from "react";
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Modal,
  TextInput,
  Alert,
} from "react-native";
import MapView, { Marker, Polygon } from "react-native-maps";
import { FontAwesome, MaterialCommunityIcons } from "@expo/vector-icons";
import { pasigCoordinates } from "@/constants/PasigCoordinates";
import {
  fetchMobileRespond,
  fetchFireResponderAccount,
  fetchStatus,
  patchStatus,
  returnToBase,
  fireOut,
  sendRequest,
} from "@/api/api";
import { useFocusEffect } from "expo-router";
import { format } from "date-fns";
import * as Location from "expo-location";
import { useSelector } from "react-redux";
import { convertToPHT } from "@/utils/convertTime";
import useResponseTime from "@/hooks/useResponseTime";

const getStatusColor = (status) => {
  switch (status) {
    case "Positive Alarm":
      return "#28a745";
    case "Negative Alarm":
      return "#6c757d";
    case "First Alarm":
      return "#ffcc00";
    case "Second Alarm":
      return "#ff9900";
    case "Third Alarm":
      return "#ff6600";
    case "Fourth Alarm":
      return "#ff4500";
    case "Fifth Alarm":
      return "#ff0000";
    case "Fire Under Control":
      return "#f0ad4e";
    case "Task Force Alpha":
      return "#007bff";
    case "Task Force Bravo":
      return "#0056b3";
    case "Task Force Charlie":
      return "#003d7a";
    case "General Alarm":
      return "#dc3545";
    case "Fire Out":
      return "#5cb85c";
    default:
      return "#002f6c"; // Default Pasig Blue
  }
};

const ArrivedScreen = ({
  ongoingResponse,
  selectedCoords,
  fetchOngoingResponse,
}) => {
  const responseTime = useResponseTime(ongoingResponse?.timeRespond)
  const [responders, setResponders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalVisible, setModalVisible] = useState(false);
  const [isRequestModalVisible, setRequestModalVisible] = useState(false);
  const [isFireOutModal, setIsFireOutModal] = useState(false);
  const [isReturnToBaseModalVisible, setIsReturnToBaseModalVisible] =
    useState(false);
  const [requestText, setRequestText] = useState("");
  const [statuses, setStatuses] = useState([]);
  const userInfo = useSelector((state) => state.user.userInfo);



  const toggleRequestModal = () => {
    setRequestModalVisible(!isRequestModalVisible);
  };

  const toggleReturnToBaseModal = () => {
    setIsReturnToBaseModalVisible(!isReturnToBaseModalVisible);
  };

  const toggleFireOutModal = () => {
    setIsFireOutModal(!isFireOutModal);
  };

  if (!ongoingResponse || (ongoingResponse.respondStatus !== "Arrived" && ongoingResponse.respondStatus !== "Request for Fire Out")) {
    return null;
  }

  const toggleModal = () => {
    fetchStatus().then((response) => {
      setStatuses(response?.data?.statuss || []);
      setModalVisible(!isModalVisible);
    });
  };

  return (
    <ScrollView contentContainerStyle={styles.scrollViewContainer}>
      <View style={styles.container}>
        <Text style={styles.arrivedText}>Arrived</Text>

        <MapView
          style={styles.map}
          initialRegion={{
            ...selectedCoords,
            latitudeDelta: 0.05,
            longitudeDelta: 0.05,
          }}
        >
          <Polygon
            coordinates={pasigCoordinates}
            strokeColor="red"
            fillColor="rgba(170, 74, 68, 0.2)"
            strokeWidth={2}
          />
          <Marker coordinate={selectedCoords} title="Fire Incident">
            <MaterialCommunityIcons name="fire" size={40} color="red" />
          </Marker>
        </MapView>

        <View style={styles.detailsContainer}>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Incident Location:</Text>
            <Text style={styles.detailValue}>{ongoingResponse.location}</Text>
          </View>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Barangay:</Text>
            <Text style={styles.detailValue}>{ongoingResponse?.barangay?.split('_').join(' ')}</Text>
          </View>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Involved:</Text>
            <Text style={styles.detailValue}>{ongoingResponse?.involve}</Text>
          </View>
          <View
            style={{
              flex: 1,
              flexDirection: "row",
              justifyContent: "space-between",
            }}
          >
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Time of Arrival:</Text>
              <Text style={styles.detailValue}>
                {format(ongoingResponse?.timeArrived, "MMM dd, yyyy hh:mm a")}
              </Text>
            </View>
            <View style={styles.detailItem}>
              <Text style={[styles.detailLabel, { textAlign: "right" }]}>
                Total Response Time:
              </Text>
              <Text style={[styles.detailValue, { textAlign: "right" }]}>
                {ongoingResponse?.totalResponseTime}
              </Text>
            </View>
          </View>
          <View style={styles.detailItem}>
            <Text style={[styles.detailLabel]}>Status:</Text>
            <View
              style={[
                styles.statusContainer,
                { backgroundColor: getStatusColor(ongoingResponse?.status) },
              ]}
            >
              <Text style={styles.statusPill}>{ongoingResponse?.status}</Text>
            </View>
          </View>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Responding Units:</Text>
            {ongoingResponse?.respondingUnit?.map((unit, index) => (
              <View
                style={[styles.statusContainer, { backgroundColor: "gray" }]}
              >
                <Text style={styles.statusPill}>
                  {unit?.responders_barangay} - {unit?.truck_unit_name}
                </Text>
              </View>
            ))}
          </View>
        </View>

        {/* Bottom Buttons */}
        <View style={styles.buttonContainer}>
          <TouchableOpacity
            style={[styles.button, styles.requestButton]}
            onPress={toggleRequestModal}
          >
            <Text style={styles.buttonText}>REQUEST</Text>
          </TouchableOpacity>

          {userInfo?.barangay !== ongoingResponse?.barangay ? (
            <TouchableOpacity
              style={[styles.button, styles.returnToBaseButton]}
              onPress={async () => {
                toggleReturnToBaseModal();
              }}
            >
              <Text style={styles.buttonText}>RETURN TO BASE</Text>
            </TouchableOpacity>
          ) : (
            <TouchableOpacity
              style={[styles.button, styles.fireOutButton, ongoingResponse?.respondStatus !== "Arrived" ? { opacity:0.4 } : {}]}
              onPress={async () => {
               setIsFireOutModal(true);
              }}
              disabled={ongoingResponse?.respondStatus !== "Arrived"}
            >
              <Text style={styles.buttonText}>{ongoingResponse?.respondStatus !== "Arrived" ? "REQUESTED FOR FIRE OUT" : "FIRE OUT"}</Text>
            </TouchableOpacity>
          )}
        </View>

        {/* Modal for Update Status */}
        <Modal
          visible={isModalVisible}
          transparent={true}
          animationType="slide"
          onRequestClose={toggleModal}
        >
          <View style={styles.modalContainer}>
            <View style={styles.modalContent}>
              <Text style={styles.modalTitle}>UPDATE STATUS</Text>
              <TouchableOpacity
                style={styles.closeButton}
                onPress={toggleModal}
              >
                <Text style={styles.closeButtonText}>X</Text>
              </TouchableOpacity>
              <View style={styles.statusButtonsContainer}>
                {statuses.map((status, index, array) => (
                  <TouchableOpacity
                    key={index}
                    style={[
                      styles.statusButton,
                      {
                        backgroundColor:
                          status?.name === "Fire Out" ? "black" : status?.color,
                        width:
                          index === array.length - 1 && array.length % 2 !== 0
                            ? "100%"
                            : "48%",
                      },
                    ]}
                    onPress={async () => {
                      const response = await patchStatus({
                        status: status?.name,
                        OngoingID: ongoingResponse?.id,
                      });

         

                      await fetchOngoingResponse();

                      setModalVisible(false);
                    }}
                  >
                    <Text style={styles.statusButtonText}>{status?.name}</Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>
          </View>
        </Modal>
        <Modal
          visible={isReturnToBaseModalVisible}
          transparent={true}
          animationType="slide"
          onRequestClose={toggleReturnToBaseModal}
        >
          <View style={styles.modalContainer}>
            <View style={styles.modalContent}>
              <TouchableOpacity
                style={styles.closeButton}
                onPress={toggleReturnToBaseModal}
              >
                <Text style={styles.closeButtonText}>X</Text>
              </TouchableOpacity>
              <MaterialCommunityIcons
                name="information-outline"
                size={40}
                color="#1E3A8A"
                style={{ textAlign: "center" }}
              />
              <Text
                style={{
                  fontSize: 20,
                  textAlign: "center",
                  marginVertical: 20,
                }}
              >
                Are you sure you want to return to base?
              </Text>
              <View style={{ flexDirection: "row", gap: 10 }}>
                <TouchableOpacity
                  style={[
                    styles.sendButton,
                    { backgroundColor: "gray", flex: 0.5 },
                  ]}
                  onPress={toggleReturnToBaseModal}
                >
                  <Text style={styles.sendButtonText}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[
                    styles.sendButton,
                    {
                      flex: 0.5,
                    },
                  ]}
                  onPress={async () => {
                    const response = await returnToBase({
                      username: userInfo?.username,
                      ongoingID: ongoingResponse?.id,
                    });


                    await fetchOngoingResponse();
                  }}
                >
                  <Text style={styles.sendButtonText}>Yes</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </Modal>
        <Modal
          visible={isFireOutModal}
          transparent={true}
          animationType="slide"
          onRequestClose={toggleFireOutModal}
        >
          <View style={styles.modalContainer}>
            <View style={styles.modalContent}>
              <TouchableOpacity
                style={styles.closeButton}
                onPress={toggleFireOutModal}
              >
                <Text style={styles.closeButtonText}>X</Text>
              </TouchableOpacity>
              <MaterialCommunityIcons
                name="information-outline"
                size={40}
                color="#1E3A8A"
                style={{ textAlign: "center" }}
              />
              <Text
                style={{
                  fontSize: 20,
                  textAlign: "center",
                  marginVertical: 20,
                }}
              >
                Are you sure you want to request for fire out?
              </Text>
              <View style={{ flexDirection: "row", gap: 10 }}>
                <TouchableOpacity
                  style={[
                    styles.sendButton,
                    { backgroundColor: "gray", flex: 0.5 },
                  ]}
                  onPress={toggleFireOutModal}
                >
                  <Text style={styles.sendButtonText}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[
                    styles.sendButton,
                    {
                      flex: 0.5,
                    },
                  ]}
                  onPress={async () => {

                    const response = await fireOut({
                      username: userInfo?.username,
                      OngoingID: ongoingResponse?.id,
                      date: new Date().toISOString(),
                    });


                    await fetchOngoingResponse();

                    toggleFireOutModal()
                  }}
                >
                  <Text style={styles.sendButtonText}>Yes</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </Modal>
        <Modal
          visible={isRequestModalVisible}
          transparent={true}
          animationType="slide"
          onRequestClose={toggleRequestModal}
        >
          <View style={styles.modalContainer}>
            <View style={styles.modalContent}>
              <Text style={styles.modalTitle}>REQUEST</Text>
              <TouchableOpacity
                style={styles.closeButton}
                onPress={toggleRequestModal}
              >
                <Text style={styles.closeButtonText}>X</Text>
              </TouchableOpacity>
              <TextInput
                style={styles.requestInput}
                placeholder="Type here..."
                placeholderTextColor="#999"
                multiline
                value={requestText}
                onChangeText={setRequestText}
              />
              <TouchableOpacity style={styles.sendButton}
              onPress={async ()=>{
                const response = await sendRequest({
                  barangay: ongoingResponse?.respondingUnit?.find(unit=> unit?.truckID === ongoingResponse?.truckID).responders_barangay,
                  username: userInfo?.username,
                  request: requestText,
                  ongoingID: ongoingResponse?.ongoingID
                })


                toggleRequestModal()
              }}
              >
                <Text style={styles.sendButtonText}>SEND</Text>
              </TouchableOpacity>
            </View>
          </View>
        </Modal>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  scrollViewContainer: {
    paddingBottom: 20,
  },
  container: {
    flex: 1,
    backgroundColor: "#fff",
  },
  arrivedText: {
    fontSize: 22,
    fontWeight: "bold",
    textAlign: "center",
    marginTop: 20,
  },
  detailsContainer: {
    padding: 20,
    backgroundColor: "#f5f5f5",
    borderRadius: 10,
    margin: 20,
  },
  detailItem: {
    marginBottom: 10,
  },
  detailLabel: {
    fontSize: 15,
    fontWeight: "bold",
    color: "#333",
  },
  detailValue: {
    fontSize: 15,
    color: "#555",
  },
  map: {
    height: 200,
    margin: 20,
    borderRadius: 10,
    overflow: "hidden",
  },
  statusContainer: {
    marginTop: 10,
    alignSelf: "flex-start",
    paddingVertical: 5,
    paddingHorizontal: 15,
    borderRadius: 10,
  },
  statusPill: {
    fontSize: 14,
    color: "#fff",
  },
  buttonContainer: {
    flexDirection: "column",
    justifyContent: "space-around",
    paddingVertical: 10,
    backgroundColor: "#fff",
    marginHorizontal: 10,
  },
  button: {
    flex: 1,
    alignItems: "center",
    paddingVertical: 15,
    marginHorizontal: 5,
    borderRadius: 5,
    marginBottom: 10,
  },
  updateStatusButton: {
    backgroundColor: "#1E3A8A",
  },
  requestButton: {
    backgroundColor: "#1E3A8A",
  },
  returnToBaseButton: {
    backgroundColor: "#16A34A",
  },
  fireOutButton: {
    backgroundColor: "#4B5563",
  },
  buttonText: {
    color: "#fff",
    fontWeight: "bold",
    fontSize: 15,
  },
  modalContainer: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "rgba(0, 0, 0, 0.5)",
  },
  modalContent: {
    width: "90%",
    padding: 20,
    backgroundColor: "#fff",
    borderRadius: 10,
    position: "relative",
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: "bold",
    marginBottom: 20,
    textAlign: "center",
  },
  closeButton: {
    position: "absolute",
    right: 10,
    top: 10,
  },
  closeButtonText: {
    fontSize: 20,
    fontWeight: "bold",
    color: "#333",
  },
  statusButtonsContainer: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
    gap: 10,
  },
  statusButton: {
    paddingVertical: 15,
    borderRadius: 5,
    alignItems: "center",
    marginBottom: 10,
  },
  statusButtonText: {
    color: "#fff",
    fontWeight: "bold",
    fontSize: 13,
  },
  requestInput: {
    height: 100,
    borderColor: "#ccc",
    borderWidth: 1,
    borderRadius: 10,
    padding: 10,
    marginBottom: 20,
    textAlignVertical: "top",
  },
  sendButton: {
    backgroundColor: "#1E3A8A",
    paddingVertical: 10,
    borderRadius: 5,
    alignItems: "center",
  },
  sendButtonText: {
    color: "#fff",
    fontWeight: "bold",
    fontSize: 15,
  },
});

export default ArrivedScreen;
