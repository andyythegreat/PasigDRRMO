// General API Response Types
export interface LoginResponse {
    emailUsername: string;
    password: string;
    isResponder: boolean;
  }
  
  export interface Announcement {
    id: number;
    date: string;
    subject: string;
    message: string;
    photo: string;
  }
  
  export interface OngoingEvent {
    id: number;
    date: string;
    caller: string;
    location: string;
    barangay: string;
    involve: string;
    status: string;
    resolved_time: string;
  }
  
  export interface Contact {
    id: number;
    contact_name: string;
    contact_number: string;
  }
  
  export interface AccountInfo {
    id: number;
    barangay: string;
    email: string;
    fullname: string;
    username: string;
    password: string;
  }
  
  // Request Payload Types
  export interface RegisterPayload {
    barangay: string;
    email: string;
    fullName: string;
    username: string;
    password: string;
    repeatPassword: string;
  }
  
  export interface LoginPayload {
    emailUsername: string;
    password: string;
  }
  
  export interface MobileLocatePayload {
    date: string;
    caller: string;
    location: string;
    longitude: string;
    latitude: string;
    barangay: string;
    photo: null;
    involve: string;
    status: string;
  }

  // Define the payload for fetch_mobile_respond
export interface MobileRespondPayload {
  username?: string;
  ongoingID?: number;
}

// Define the structure of the response
export interface MobileRespondResponse {
  statusCode: number;
  success: boolean;
  messages: string[];
  data: {
    rows_returned: number;
    mobileresponds: MobileRespond[];
  };
}

// Define the individual mobile respond data
export interface MobileRespond {
  username: string;
  location: string;
  longitude: number;
  latitude: number;
  timeRespond: string;
  respondStatus: string;
  timeArrived: string;
  ongoingID: number;
}
