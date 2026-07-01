import { z } from 'zod';

export const createProductSchema = z.object({
  name: z
    .string()
    .min(3, 'Nome deve ter pelo menos 3 caracteres')
    .max(255, 'Nome não pode exceder 255 caracteres'),
  description: z.string().max(1000, 'Descrição não pode exceder 1000 caracteres').optional(),
  price: z.number().min(0.01, 'Preço deve ser maior que 0').max(9999999.99, 'Preço muito alto'),
  cost_price: z.number().min(0, 'Custo não pode ser negativo').max(9999999.99, 'Custo muito alto'),
  quantity: z
    .number()
    .min(0, 'Quantidade não pode ser negativa')
    .max(999999, 'Quantidade muito alta')
    .int('Quantidade deve ser um número inteiro'),
  category_id: z.number().min(1, 'Selecione uma categoria').int('ID da categoria inválido'),
  active: z.boolean().default(true),
});

export const updateProductSchema = z.object({
  name: z
    .string()
    .min(3, 'Nome deve ter pelo menos 3 caracteres')
    .max(255, 'Nome não pode exceder 255 caracteres'),
  price: z.number().min(0.01, 'Preço deve ser maior que 0').max(9999999.99, 'Preço muito alto'),
  quantity: z
    .number()
    .min(0, 'Quantidade não pode ser negativa')
    .max(999999, 'Quantidade muito alta')
    .int('Quantidade deve ser um número inteiro'),
  active: z.boolean().optional(),
});

export type CreateProductInput = z.infer<typeof createProductSchema>;
export type UpdateProductInput = z.infer<typeof updateProductSchema>;
