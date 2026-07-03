import { z } from 'zod';
import type { Category } from '@/types';

export const createCategorySchema = z.object({
  name: z
    .string()
    .min(2, 'Nome deve ter pelo menos 2 caracteres')
    .max(100, 'Nome não pode exceder 100 caracteres'),
});

export const updateCategorySchema = z.object({
  name: z
    .string()
    .min(2, 'Nome deve ter pelo menos 2 caracteres')
    .max(100, 'Nome não pode exceder 100 caracteres'),
});

// Schema com validação de duplicidade para criar categoria
export const createCategorySchemaWithValidation = (existingCategories: Category[]) =>
  createCategorySchema.refine(
    (data) => !existingCategories.some((cat) => cat.name.toLowerCase() === data.name.toLowerCase()),
    {
      message: 'Já existe uma categoria com este nome',
      path: ['name'],
    }
  );

// Schema com validação de duplicidade para atualizar categoria
export const updateCategorySchemaWithValidation = (
  existingCategories: Category[],
  currentCategoryId: number
) =>
  updateCategorySchema.refine(
    (data) =>
      !existingCategories.some(
        (cat) => cat.name.toLowerCase() === data.name.toLowerCase() && cat.id !== currentCategoryId
      ),
    {
      message: 'Já existe uma categoria com este nome',
      path: ['name'],
    }
  );

export type CreateCategoryInput = z.infer<typeof createCategorySchema>;
export type UpdateCategoryInput = z.infer<typeof updateCategorySchema>;

export const apiErrorSchema = z.object({
  response: z.object({
    data: z.object({
      errors: z.record(z.array(z.string())).optional(),
      message: z.string().optional(),
    }),
  }),
});
