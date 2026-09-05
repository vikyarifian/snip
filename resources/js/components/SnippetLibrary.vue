<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <div class="w-full md:w-80 bg-white border-r border-slate-200 flex flex-col h-screen overflow-hidden">
      <!-- Header -->
      <div class="p-4 border-b border-slate-100">
        <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
          <span class="text-indigo-600">✂</span> Snip
        </h1>
        <p class="text-xs text-slate-500">Kompilasi template & script operasional</p>
      </div>

      <!-- Filters -->
      <div class="p-4 border-b border-slate-100 space-y-3 bg-slate-50/50">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Cari Script</label>
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Cari judul atau teks..." 
            class="w-full text-sm px-3 py-1.5 border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
          />
        </div>
        
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Departemen</label>
            <select 
              v-model="selectedDepartment" 
              class="w-full text-sm px-2 py-1.5 border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
            >
              <option value="">Semua</option>
              <option value="Keuangan">Keuangan</option>
              <option value="SDM">SDM</option>
              <option value="Umum">Umum</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Cari Tag</label>
            <input 
              type="text" 
              v-model="searchTag"
              placeholder="Misal: bank" 
              class="w-full text-sm px-3 py-1.5 border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
            />
          </div>
        </div>
      </div>

      <!-- Snippet list -->
      <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
        <div v-if="loading && snippets.length === 0" class="p-4 text-center text-sm text-slate-500">
          Memuat data...
        </div>
        <div v-else-if="snippets.length === 0" class="p-4 text-center text-sm text-slate-500">
          Tidak ada template ditemukan.
        </div>
        <div 
          v-for="snippet in snippets" 
          :key="snippet.id"
          @click="selectSnippet(snippet)"
          :class="['p-4 cursor-pointer transition-colors text-left hover:bg-slate-50', selectedSnippet && selectedSnippet.id === snippet.id ? 'bg-indigo-50/70 border-l-4 border-indigo-600 hover:bg-indigo-50/70' : '']"
        >
          <div class="flex items-center justify-between gap-2 mb-1">
            <h2 class="font-semibold text-sm text-slate-800 truncate" :title="snippet.title">
              {{ snippet.title }}
            </h2>
            <span :class="['text-[10px] px-1.5 py-0.5 font-semibold rounded shrink-0', getDeptClass(snippet.department)]">
              {{ snippet.department }}
            </span>
          </div>
          <p class="text-xs text-slate-500 line-clamp-2 mb-2">
            {{ snippet.content }}
          </p>
          <div class="flex flex-wrap gap-1">
            <span 
              v-for="tag in snippet.tags" 
              :key="tag" 
              class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.5 rounded border border-slate-200"
            >
              #{{ tag }}
            </span>
          </div>
        </div>
      </div>

      <!-- Action Bar (Manager Only) -->
      <div v-if="currentUser && currentUser.role === 'manager'" class="p-4 border-t border-slate-200 bg-slate-50">
        <button 
          @click="createNewSnippet"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm py-2 px-4 rounded shadow-sm transition-colors"
        >
          + Buat Template Baru
        </button>
      </div>
    </div>

    <!-- Content Pane -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Top info bar / Error messages -->
      <div v-if="errorMessage" class="bg-rose-50 border-b border-rose-200 px-6 py-2.5 text-xs text-rose-700 flex justify-between items-center shrink-0">
        <span>{{ errorMessage }}</span>
        <button @click="errorMessage = ''" class="font-bold text-rose-500 hover:text-rose-700">✕</button>
      </div>

      <!-- Active Area -->
      <div class="flex-1 overflow-y-auto p-6 md:p-8">
        
        <!-- Viewing Snippet -->
        <div v-if="selectedSnippet && !isEditing && !isCreating" class="max-w-3xl mx-auto bg-white rounded-lg border border-slate-200 shadow-sm p-6 space-y-6">
          <div class="flex justify-between items-start border-b border-slate-100 pb-4">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <h2 class="text-xl font-bold text-slate-800">{{ selectedSnippet.title }}</h2>
                <span :class="['text-xs px-2 py-0.5 font-semibold rounded', getDeptClass(selectedSnippet.department)]">
                  {{ selectedSnippet.department }}
                </span>
              </div>
              <p class="text-xs text-slate-400">
                Dibuat oleh: <span class=