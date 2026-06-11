import api from './api';
import type { AuthResponse } from '../types/user.type';

export const AuthService = {
  async register(data: any) {
    const response = await api.post<AuthResponse>('/v2/register', data);
    return response.data;
  },
  async login(data: any) {
    const response = await api.post<AuthResponse>('/login', data);
    return response.data;
  }
};
