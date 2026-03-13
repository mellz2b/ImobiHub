export type DealType = "alugar" | "comprar" | "imovel-novo" | "leilao";

export type PropertyType =
  | "apartamento"
  | "casa"
  | "imovel-comercial"
  | "terreno";

export type Property = {
  id: string;
  title: string;
  dealType: DealType;
  propertyType: PropertyType;
  city: string;
  neighborhood: string;
  price: number;
  area: number;
  bedrooms: number;
  bathrooms: number;
  description: string;
  sustainabilityTag: string;
  photos: string[];
  sold: boolean;
  createdAt: string;
};

const STORAGE_KEY = "imobihub_properties_v1";

const initialProperties: Property[] = [
  {
    id: "apto-centro-001",
    title: "Apartamento compacto no Centro",
    dealType: "comprar",
    propertyType: "apartamento",
    city: "Curitiba",
    neighborhood: "Centro",
    price: 385000,
    area: 62,
    bedrooms: 2,
    bathrooms: 2,
    description:
      "Apartamento com ventilacao cruzada, boa iluminacao natural e acesso a transporte publico.",
    sustainabilityTag: "Mobilidade urbana",
    photos: ["https://images.unsplash.com/photo-1494526585095-c41746248156?w=1200&q=80&auto=format&fit=crop"],
    sold: false,
    createdAt: "2026-03-10T09:00:00.000Z",
  },
  {
    id: "casa-bairro-002",
    title: "Casa familiar com quintal permeavel",
    dealType: "alugar",
    propertyType: "casa",
    city: "Curitiba",
    neighborhood: "Bacacheri",
    price: 720000,
    area: 145,
    bedrooms: 3,
    bathrooms: 2,
    description:
      "Imovel com area verde, reuso de agua para jardim e espaco para horta urbana.",
    sustainabilityTag: "Infraestrutura verde",
    photos: ["https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1200&q=80&auto=format&fit=crop"],
    sold: false,
    createdAt: "2026-03-09T15:30:00.000Z",
  },
];

export function getInitialProperties(): Property[] {
  return [...initialProperties];
}

export function loadProperties(): Property[] {
  if (typeof window === "undefined") {
    return initialProperties;
  }

  const stored = window.localStorage.getItem(STORAGE_KEY);
  if (!stored) {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(initialProperties));
    return initialProperties;
  }

  try {
    const parsed = JSON.parse(stored) as Partial<Property>[];
    if (!Array.isArray(parsed)) {
      return initialProperties;
    }

    // Keep old localStorage records compatible with new filters.
    return parsed.map((item) => ({
      id: item.id ?? `imovel-${Date.now()}`,
      title: item.title ?? "Imovel sem titulo",
      dealType: item.dealType ?? "comprar",
      propertyType: item.propertyType ?? "apartamento",
      city: item.city ?? "Cidade nao informada",
      neighborhood: item.neighborhood ?? "Bairro nao informado",
      price: Number(item.price ?? 0),
      area: Number(item.area ?? 0),
      bedrooms: Number(item.bedrooms ?? 0),
      bathrooms: Number(item.bathrooms ?? 0),
      description: item.description ?? "",
      sustainabilityTag: item.sustainabilityTag ?? "Sem tag",
      photos: Array.isArray(item.photos) ? item.photos : [],
      sold: Boolean(item.sold),
      createdAt: item.createdAt ?? new Date().toISOString(),
    }));
  } catch {
    return initialProperties;
  }
}

export function saveProperties(properties: Property[]) {
  if (typeof window === "undefined") {
    return;
  }
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(properties));
}
