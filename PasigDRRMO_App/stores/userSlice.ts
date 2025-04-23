import { createSlice } from '@reduxjs/toolkit';

// Initial state for the user
const initialState = {
  userInfo: {
    fullName: '',
    email: '',
    isResponder: false,
    username: ''
  },
};

const userSlice = createSlice({
  name: 'user',
  initialState,
  reducers: {
    // Set user info action
    setUserInfo: (state, action) => {
      state.userInfo = action.payload;
    },
    // Clear user info action
    clearUserInfo: (state) => {
      state.userInfo = {
        fullName: '',
        email: '',
        isResponder: false,
        username: ''
      };
    },
  },
});

export const { setUserInfo, clearUserInfo } = userSlice.actions;

export default userSlice.reducer;
