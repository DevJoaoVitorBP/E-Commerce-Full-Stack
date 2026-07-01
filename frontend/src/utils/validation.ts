import { ZodError } from 'zod';

export interface ValidationErrors {
  [key: string]: string;
}

export const getZodErrors = (error: ZodError): ValidationErrors => {
  const errors: ValidationErrors = {};
  error.errors.forEach((err) => {
    const path = err.path.join('.');
    errors[path] = err.message;
  });
  return errors;
};

export const getFirstError = (errors: ValidationErrors): string | null => {
  const firstKey = Object.keys(errors)[0];
  return firstKey ? errors[firstKey] : null;
};
