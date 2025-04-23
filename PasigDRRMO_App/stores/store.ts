import { configureStore } from '@reduxjs/toolkit';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { persistStore, persistReducer, FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER } from 'redux-persist';
import userReducer from './userSlice';


// Configuration for redux-persist
const persistConfig = {
  key: 'root', // Key for the storage
  storage: AsyncStorage, // Storage engine (AsyncStorage for React Native)
};

// Wrap the user reducer with persistReducer
const persistedUserReducer = persistReducer(persistConfig, userReducer);

// Configure the store with the persisted reducer
export const store = configureStore({
  reducer: {
    user: persistedUserReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
      },
    }),
});

// Create a persistor for the store
export const persistor = persistStore(store);
