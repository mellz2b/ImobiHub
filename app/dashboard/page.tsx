"use client";

import { ChangeEvent, FormEvent, useEffect, useMemo, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import {
  DealType,
  getInitialProperties,
  loadProperties,
  Property,
  PropertyType,
  saveProperties,
} from "@/lib/property-store";

type DraftProperty = {
  title: string;
  dealType: DealType;
  propertyType: PropertyType;
  city: string;
  neighborhood: string;
  price: string;
  area: string;
  bedrooms: string;
  bathrooms: string;
  description: string;
  sustainabilityTag: string;
};

const emptyDraft: DraftProperty = {
  title: "",
  dealType: "comprar",
  propertyType: "apartamento",
  city: "",
  neighborhood: "",
  price: "",
  area: "",
  bedrooms: "",
  bathrooms: "",
  description: "",
  sustainabilityTag: "",
};

function toCurrency(value: number) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
    maximumFractionDigits: 0,
  }).format(value);
}

function formatDealType(value: DealType) {
  const labels: Record<DealType, string> = {
    alugar: "Alugar",
    comprar: "Comprar",
    "imovel-novo": "Imovel novo",
    leilao: "Leilao",
  };
  return labels[value];
}

function formatPropertyType(value: PropertyType) {
  const labels: Record<PropertyType, string> = {
    apartamento: "Apartamento",
    casa: "Casa",
    "imovel-comercial": "Imovel comercial",
    terreno: "Terreno",
  };
  return labels[value];
}

function readFileAsDataUrl(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result));
    reader.onerror = () => reject(new Error("Falha ao ler imagem."));
    reader.readAsDataURL(file);
  });
}

export default function DashboardPage() {
  const [properties, setProperties] = useState<Property[]>(() => getInitialProperties());
  const [draft, setDraft] = useState<DraftProperty>(emptyDraft);
  const [photoFiles, setPhotoFiles] = useState<File[]>([]);

  useEffect(() => {
    setProperties(loadProperties());
  }, []);

  const availableCount = useMemo(
    () => properties.filter((item) => !item.sold).length,
    [properties],
  );

  const soldCount = properties.length - availableCount;

  const updateAndPersist = (next: Property[]) => {
    setProperties(next);
    saveProperties(next);
  };

  const handleInputChange = (
    event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>,
  ) => {
    const { name, value } = event.target;
    setDraft((prev) => ({ ...prev, [name]: value }));
  };

  const handlePhotoSelection = (event: ChangeEvent<HTMLInputElement>) => {
    const files = event.target.files ? Array.from(event.target.files) : [];
    setPhotoFiles(files);
  };

  const addProperty = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const photoData = await Promise.all(photoFiles.map(readFileAsDataUrl));

    const nextProperty: Property = {
      id: `imovel-${Date.now()}`,
      title: draft.title,
      dealType: draft.dealType,
      propertyType: draft.propertyType,
      city: draft.city,
      neighborhood: draft.neighborhood,
      price: Number(draft.price),
      area: Number(draft.area),
      bedrooms: Number(draft.bedrooms),
      bathrooms: Number(draft.bathrooms),
      description: draft.description,
      sustainabilityTag: draft.sustainabilityTag,
      photos: photoData,
      sold: false,
      createdAt: new Date().toISOString(),
    };

    const nextList = [nextProperty, ...properties];
    updateAndPersist(nextList);
    setDraft(emptyDraft);
    setPhotoFiles([]);
  };

  const updatePrice = (id: string, newPrice: number) => {
    const next = properties.map((item) =>
      item.id === id ? { ...item, price: newPrice } : item,
    );
    updateAndPersist(next);
  };

  const toggleSold = (id: string) => {
    const next = properties.map((item) =>
      item.id === id ? { ...item, sold: !item.sold } : item,
    );
    updateAndPersist(next);
  };

  return (
    <div className="min-h-screen bg-[radial-gradient(circle_at_18%_15%,#e5eefb_0%,#f6f9ff_44%,#fdfefe_100%)] px-5 py-8 text-[#091f44] md:px-10">
      <div className="mx-auto max-w-6xl space-y-8">
        <header className="rounded-3xl border border-[#c8d8ef] bg-white p-6 shadow-sm">
          <div className="flex flex-wrap items-start justify-between gap-4">
            <div>
              <Image
                src="/imobihub-logo.svg"
                alt="Logo ImobiHub"
                width={170}
                height={50}
                className="h-10 w-auto"
                priority
              />
              <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#16467e]">
                Dashboard da imobiliaria
              </p>
              <h1 className="mt-1 text-3xl font-semibold">Gerenciador de imoveis</h1>
              <p className="mt-2 text-sm text-[#42597a]">
                Controle de anuncios com visual alinhado a identidade da ImobiHub.
              </p>
            </div>
            <Link
              href="/"
              className="rounded-full border border-[#aac0df] px-4 py-2 text-sm font-medium text-[#0c2f5d] transition hover:border-[#0c2f5d]"
            >
              Ver catalogo publico
            </Link>
          </div>
          <div className="mt-5 grid gap-4 sm:grid-cols-3">
            <article className="rounded-2xl bg-[#edf4ff] p-4">
              <p className="text-xs uppercase tracking-wide text-[#16467e]">Total</p>
              <strong className="text-2xl">{properties.length}</strong>
            </article>
            <article className="rounded-2xl bg-[#e6f0ff] p-4">
              <p className="text-xs uppercase tracking-wide text-[#0f3f75]">Disponiveis</p>
              <strong className="text-2xl">{availableCount}</strong>
            </article>
            <article className="rounded-2xl bg-[#f1f4f9] p-4">
              <p className="text-xs uppercase tracking-wide text-[#4f6482]">Vendidos</p>
              <strong className="text-2xl">{soldCount}</strong>
            </article>
          </div>
        </header>

        <section className="rounded-3xl border border-[#d3e0f2] bg-white p-6 shadow-sm">
          <h2 className="text-xl font-semibold">Adicionar imovel</h2>
          <form onSubmit={addProperty} className="mt-4 grid gap-4 md:grid-cols-2">
            <input required name="title" value={draft.title} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Titulo do imovel" />
            <label className="text-sm text-[#42597a]">
              <span className="mb-1 block">Tipo de negocio</span>
              <select
                required
                name="dealType"
                value={draft.dealType}
                onChange={handleInputChange}
                className="w-full rounded-xl border border-[#bfd0e8] px-4 py-3"
              >
                <option value="alugar">Alugar</option>
                <option value="comprar">Comprar</option>
                <option value="imovel-novo">Imovel novo</option>
                <option value="leilao">Leilao</option>
              </select>
            </label>
            <label className="text-sm text-[#42597a]">
              <span className="mb-1 block">Tipo de imovel</span>
              <select
                required
                name="propertyType"
                value={draft.propertyType}
                onChange={handleInputChange}
                className="w-full rounded-xl border border-[#bfd0e8] px-4 py-3"
              >
                <option value="apartamento">Apartamento</option>
                <option value="casa">Casa</option>
                <option value="imovel-comercial">Imovel comercial</option>
                <option value="terreno">Terreno</option>
              </select>
            </label>
            <input required name="city" value={draft.city} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Cidade" />
            <input required name="neighborhood" value={draft.neighborhood} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Bairro" />
            <input required name="price" type="number" min="1" value={draft.price} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Preco" />
            <input required name="area" type="number" min="1" value={draft.area} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Area (m2)" />
            <input required name="bedrooms" type="number" min="0" value={draft.bedrooms} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Quartos" />
            <input required name="bathrooms" type="number" min="0" value={draft.bathrooms} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Banheiros" />
            <input required name="sustainabilityTag" value={draft.sustainabilityTag} onChange={handleInputChange} className="rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Tag de sustentabilidade" />
            <label className="md:col-span-2 rounded-xl border border-dashed border-[#9fb7d8] px-4 py-3 text-sm text-[#4a6183]">
              Subir fotos (JPG/PNG)
              <input multiple type="file" accept="image/*" onChange={handlePhotoSelection} className="mt-2 block w-full" />
            </label>
            <textarea required name="description" value={draft.description} onChange={handleInputChange} className="md:col-span-2 min-h-28 rounded-xl border border-[#bfd0e8] px-4 py-3" placeholder="Descricao" />
            <button className="md:col-span-2 rounded-xl bg-[#0c2f5d] px-4 py-3 text-white transition hover:bg-[#16467e]" type="submit">
              Cadastrar imovel
            </button>
          </form>
        </section>

        <section className="space-y-4">
          <h2 className="text-xl font-semibold">Anuncios cadastrados</h2>
          {properties.map((item) => (
            <article key={item.id} className="rounded-3xl border border-[#d3e0f2] bg-white p-4 shadow-sm">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h3 className="text-lg font-semibold">{item.title}</h3>
                  <p className="text-sm text-[#4a6183]">{item.neighborhood}, {item.city}</p>
                  <p className="mt-1 flex flex-wrap gap-2 text-xs text-[#334a6b]">
                    <span className="rounded-full bg-[#eef4fd] px-2 py-1">{formatDealType(item.dealType)}</span>
                    <span className="rounded-full bg-[#eef4fd] px-2 py-1">{formatPropertyType(item.propertyType)}</span>
                  </p>
                </div>
                <span className={`rounded-full px-3 py-1 text-xs font-semibold ${item.sold ? "bg-[#e8ecf4] text-[#455b7a]" : "bg-[#deebfa] text-[#0f3f75]"}`}>
                  {item.sold ? "Vendido" : "Disponivel"}
                </span>
              </div>

              <div className="mt-4 grid gap-3 sm:grid-cols-3">
                <label className="text-sm">
                  <span className="mb-1 block text-[#4a6183]">Editar preco</span>
                  <input
                    type="number"
                    min="1"
                    defaultValue={item.price}
                    className="w-full rounded-xl border border-[#bfd0e8] px-3 py-2"
                    onBlur={(event) => updatePrice(item.id, Number(event.target.value))}
                  />
                </label>
                <p className="text-sm text-[#334a6b] sm:self-end">Atual: <strong>{toCurrency(item.price)}</strong></p>
                <button
                  type="button"
                  onClick={() => toggleSold(item.id)}
                  className="rounded-xl border border-[#0c2f5d] px-3 py-2 text-sm font-medium text-[#0c2f5d] transition hover:bg-[#0c2f5d] hover:text-white"
                >
                  {item.sold ? "Marcar como disponivel" : "Marcar como vendido"}
                </button>
              </div>
            </article>
          ))}
        </section>
      </div>
    </div>
  );
}
