"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useMemo, useState } from "react";
import {
  DealType,
  getInitialProperties,
  loadProperties,
  Property,
  PropertyType,
} from "@/lib/property-store";

function toCurrency(value: number) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
    maximumFractionDigits: 0,
  }).format(value);
}

type SortMode = "default" | "latest" | "affordable";

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

export default function HomePage() {
  const [properties, setProperties] = useState<Property[]>(() => getInitialProperties());
  const [showSold, setShowSold] = useState(false);
  const [dealType, setDealType] = useState<DealType>("comprar");
  const [propertyTypeFilter, setPropertyTypeFilter] = useState<"todos" | PropertyType>("todos");
  const [searchDraft, setSearchDraft] = useState("");
  const [searchQuery, setSearchQuery] = useState("");
  const [sortMode, setSortMode] = useState<SortMode>("default");

  useEffect(() => {
    setProperties(loadProperties());
  }, []);

  const catalog = useMemo(() => {
    let list = showSold ? [...properties] : properties.filter((item) => !item.sold);

    list = list.filter((item) => item.dealType === dealType);

    if (propertyTypeFilter !== "todos") {
      list = list.filter((item) => item.propertyType === propertyTypeFilter);
    }

    const normalizedQuery = searchQuery.trim().toLowerCase();
    if (normalizedQuery) {
      list = list.filter((item) => {
        const content = `${item.title} ${item.city} ${item.neighborhood} ${item.description}`.toLowerCase();
        return content.includes(normalizedQuery);
      });
    }

    if (sortMode === "latest") {
      list = [...list].sort(
        (a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime(),
      );
    }

    if (sortMode === "affordable") {
      list = [...list].sort((a, b) => a.price - b.price);
    }

    return list;
  }, [dealType, properties, propertyTypeFilter, searchQuery, showSold, sortMode]);

  const runSearch = () => {
    setSearchQuery(searchDraft);
    setSortMode("default");
  };

  const applySuggestion = (mode: SortMode, deal: DealType, type: "todos" | PropertyType) => {
    setSortMode(mode);
    setDealType(deal);
    setPropertyTypeFilter(type);
    setSearchDraft("");
    setSearchQuery("");
  };

  return (
    <div className="min-h-screen bg-[linear-gradient(160deg,#f7faff_0%,#eef3fb_44%,#fdfefe_100%)] px-5 py-8 text-[#091f44] md:px-10">
      <div className="mx-auto max-w-6xl">
        <header className="relative overflow-hidden rounded-3xl border border-[#c8d8ef] bg-white p-7 shadow-sm">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <Image
                src="/imobihub-logo.svg"
                alt="Logo ImobiHub"
                width={180}
                height={52}
                className="h-10 w-auto sm:h-12"
                priority
              />
              <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#16467e]">ImobiHub</p>
            </div>
            <nav className="flex flex-wrap items-center gap-2 text-sm">
              <button type="button" className="rounded-full bg-[#0c2f5d] px-3 py-1.5 font-semibold text-white hover:bg-[#16467e]">Entrar</button>
              <Link href="/dashboard" className="rounded-full bg-[#0c2f5d] px-3 py-1.5 font-semibold text-white hover:bg-[#16467e]">Anunciar</Link>
            </nav>
          </div>
          <h1 className="mt-4 max-w-2xl text-4xl font-semibold leading-tight">
            Encontre seu lar
          </h1>
          <p className="mt-3 max-w-2xl text-[#3a4d6e]">
            Busque por tipo de negocio, categoria e localidade com uma experiencia clara e objetiva.
          </p>

          <div className="mt-5 rounded-2xl border border-[#d1deef] bg-[#f9fbff] p-3">
            <div className="flex flex-wrap gap-2">
              {(["alugar", "comprar", "imovel-novo", "leilao"] as DealType[]).map((item) => (
                <button
                  key={item}
                  type="button"
                  onClick={() => setDealType(item)}
                  className={`rounded-xl px-4 py-2 text-sm font-semibold transition ${dealType === item ? "bg-[#e1ebfb] text-[#0f3f75]" : "bg-white text-[#42597a] hover:bg-[#eef4fd]"}`}
                >
                  {formatDealType(item)}
                </button>
              ))}
            </div>

            <div className="mt-3 grid gap-3 md:grid-cols-[220px_1fr_140px]">
              <select
                value={propertyTypeFilter}
                onChange={(event) => setPropertyTypeFilter(event.target.value as "todos" | PropertyType)}
                className="rounded-xl border border-[#bfd0e8] bg-white px-4 py-3 text-sm"
              >
                <option value="todos">Todos os tipos</option>
                <option value="apartamento">Apartamento</option>
                <option value="casa">Casa</option>
                <option value="imovel-comercial">Imovel comercial</option>
                <option value="terreno">Terreno</option>
              </select>
              <input
                value={searchDraft}
                onChange={(event) => setSearchDraft(event.target.value)}
                className="rounded-xl border border-[#bfd0e8] bg-white px-4 py-3 text-sm"
                placeholder="Digite cidade, bairro ou caracteristicas"
              />
              <button
                type="button"
                onClick={runSearch}
                className="rounded-xl bg-[#0c2f5d] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#16467e]"
              >
                Buscar
              </button>
            </div>
          </div>

          <div className="mt-5 flex flex-wrap gap-3">
            <button
              type="button"
              onClick={() => setShowSold((prev) => !prev)}
              className="rounded-full border border-[#aac0df] px-5 py-2 text-sm font-semibold text-[#0c2f5d] transition hover:border-[#0c2f5d]"
            >
              {showSold ? "Ocultar vendidos" : "Mostrar vendidos"}
            </button>
          </div>
        </header>

        <section id="servicos" className="mt-8 scroll-mt-24">
          <div className="grid gap-4 lg:grid-cols-3">
            <article className="rounded-2xl border border-[#d3e0f2] bg-white p-4">
              <h2 className="text-xl font-semibold">Garantia locaticia</h2>
              <p className="mt-1 text-sm text-[#4a6183]">Coberturas que protegem proprietario e inquilino durante toda a locacao.</p>
            </article>
            <article className="rounded-2xl border border-[#d3e0f2] bg-white p-4">
              <h2 className="text-xl font-semibold">Guia para alugar</h2>
              <p className="mt-1 text-sm text-[#4a6183]">Checklist pratico para decidir quando alugar e como avaliar o contrato.</p>
            </article>
            <article className="rounded-2xl border border-[#d3e0f2] bg-white p-4">
              <h2 className="text-xl font-semibold">Fale conosco</h2>
              <p className="mt-1 text-sm text-[#4a6183]">Canal direto para tirar duvidas sobre anuncios, compra e aluguel.</p>
            </article>
          </div>
        </section>

        <section className="mt-10">
          <h2 className="text-3xl font-semibold">Sugestoes de imoveis que voce vai amar</h2>
          <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <button type="button" onClick={() => applySuggestion("default", "comprar", "apartamento")} className="rounded-2xl border border-[#d3e0f2] bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">Apartamentos em alta mais procurados</button>
            <button type="button" onClick={() => applySuggestion("latest", "comprar", "todos")} className="rounded-2xl border border-[#d3e0f2] bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">Imoveis que acabaram de chegar</button>
            <button type="button" onClick={() => applySuggestion("affordable", "comprar", "todos")} className="rounded-2xl border border-[#d3e0f2] bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">Oportunidade imperdivel</button>
            <button type="button" onClick={() => applySuggestion("latest", "alugar", "apartamento")} className="rounded-2xl border border-[#d3e0f2] bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">Novos apartamentos para alugar</button>
          </div>
        </section>

        <section className="mt-8">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 className="text-2xl font-semibold">Resultados ({catalog.length})</h2>
            <p className="text-sm text-[#4a6183]">
              Filtro ativo: {formatDealType(dealType)}
              {propertyTypeFilter !== "todos" ? ` • ${formatPropertyType(propertyTypeFilter)}` : ""}
            </p>
          </div>

          <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
          {catalog.map((item) => (
            <article
              key={item.id}
              className={`group overflow-hidden rounded-3xl border border-[#d3e0f2] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md ${item.sold ? "opacity-70" : ""}`}
            >
              <div className="relative h-44 w-full overflow-hidden bg-[#e6eef9]">
                {item.photos[0] ? (
                  <img
                    src={item.photos[0]}
                    alt={item.title}
                    className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                  />
                ) : (
                  <div className="flex h-full items-center justify-center text-sm text-[#536a8d]">
                    Sem foto
                  </div>
                )}
                <span className={`absolute top-3 left-3 rounded-full px-3 py-1 text-xs font-semibold ${item.sold ? "bg-[#e8ecf4] text-[#455b7a]" : "bg-[#deebfa] text-[#0f3f75]"}`}>
                  {item.sold ? "Vendido" : "Disponivel"}
                </span>
              </div>

              <div className="space-y-3 p-4">
                <div>
                  <h2 className="text-lg font-semibold">{item.title}</h2>
                  <p className="text-sm text-[#4a6183]">{item.neighborhood}, {item.city}</p>
                  <p className="mt-1 flex flex-wrap gap-2 text-xs text-[#334a6b]">
                    <span className="rounded-full bg-[#eef4fd] px-2 py-1">{formatDealType(item.dealType)}</span>
                    <span className="rounded-full bg-[#eef4fd] px-2 py-1">{formatPropertyType(item.propertyType)}</span>
                  </p>
                </div>
                <p className="text-sm text-[#334a6b]">{item.description}</p>
                <div className="flex flex-wrap gap-2 text-xs">
                  <span className="rounded-full bg-[#edf3fc] px-2 py-1">{item.area} m2</span>
                  <span className="rounded-full bg-[#edf3fc] px-2 py-1">{item.bedrooms} quartos</span>
                  <span className="rounded-full bg-[#edf3fc] px-2 py-1">{item.bathrooms} banheiros</span>
                  <span className="rounded-full bg-[#d8e8fb] px-2 py-1 text-[#0f3f75]">{item.sustainabilityTag}</span>
                </div>
                <p className="text-xl font-semibold text-[#0b2f5f]">{toCurrency(item.price)}</p>
              </div>
            </article>
          ))}
          </div>

          {catalog.length === 0 && (
            <div className="rounded-2xl border border-dashed border-[#bfd0e8] bg-white p-6 text-sm text-[#4a6183]">
              Nenhum imovel encontrado para os filtros atuais. Ajuste os criterios e tente novamente.
            </div>
          )}
        </section>

      </div>
    </div>
  );
}
