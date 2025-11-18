{{-- Professional Image Editor Modal - Photoshop-like Interface --}}
<div x-show="isOpen"
     x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75"
     style="display: none; z-index: 9999;"
     @keydown.escape.window="closeEditor()">

    <div class="w-full h-full flex flex-col bg-gray-900 text-white"
         @click.away="if (!editingText) closeEditor()">

        {{-- Top Toolbar --}}
        <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
            <div class="flex items-center space-x-2">
                <h3 class="text-lg font-semibold">Resim Düzenleyici</h3>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="undo()"
                        :disabled="historyIndex <= 0"
                        :class="historyIndex <= 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                        class="px-3 py-1 rounded text-sm"
                        title="Geri Al (Ctrl+Z)">
                    <i class="fas fa-undo"></i>
                </button>
                <button @click="redo()"
                        :disabled="historyIndex >= history.length - 1"
                        :class="historyIndex >= history.length - 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                        class="px-3 py-1 rounded text-sm"
                        title="Yinele (Ctrl+Shift+Z)">
                    <i class="fas fa-redo"></i>
                </button>
                <button @click="closeEditor()"
                        class="px-3 py-1 rounded text-sm hover:bg-red-600">
                    <i class="fas fa-times"></i> Kapat
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">

            {{-- Left Toolbar --}}
            <div class="w-16 bg-gray-800 border-r border-gray-700 flex flex-col items-center py-2 space-y-2">
                {{-- Tools --}}
                <button @click="setTool('select')"
                        :class="activeTool === 'select' ? 'bg-blue-600' : 'hover:bg-gray-700'"
                        class="w-12 h-12 rounded flex items-center justify-center"
                        title="Seçim (V)">
                    <i class="fas fa-mouse-pointer"></i>
                </button>

                <button @click="setTool('crop')"
                        :class="activeTool === 'crop' ? 'bg-blue-600' : 'hover:bg-gray-700'"
                        class="w-12 h-12 rounded flex items-center justify-center"
                        title="Kırp (C)">
                    <i class="fas fa-crop"></i>
                </button>

                <button @click="setTool('eraser')"
                        :class="activeTool === 'eraser' ? 'bg-blue-600' : 'hover:bg-gray-700'"
                        class="w-12 h-12 rounded flex items-center justify-center"
                        title="Silgi (E)">
                    <i class="fas fa-eraser"></i>
                </button>

                <button @click="setTool('text')"
                        :class="activeTool === 'text' ? 'bg-blue-600' : 'hover:bg-gray-700'"
                        class="w-12 h-12 rounded flex items-center justify-center"
                        title="Metin (T)">
                    <i class="fas fa-font"></i>
                </button>

                <button @click="setTool('pan')"
                        :class="activeTool === 'pan' ? 'bg-blue-600' : 'hover:bg-gray-700'"
                        class="w-12 h-12 rounded flex items-center justify-center"
                        title="Taşı">
                    <i class="fas fa-hand-paper"></i>
                </button>
            </div>

            {{-- Main Canvas Area --}}
            <div class="flex-1 flex flex-col overflow-hidden bg-gray-700">
                <div class="flex-1 overflow-auto p-4 flex items-center justify-center"
                     x-ref="canvasWrapper">
                    <canvas id="image-editor-canvas"
                            class="bg-white shadow-2xl"
                            style="cursor: default;"></canvas>
                </div>
            </div>

            {{-- Right Properties Panel --}}
            <div class="w-80 bg-gray-800 border-l border-gray-700 overflow-y-auto flex-none">

                {{-- Tool Properties --}}
                <div class="p-4 space-y-4">

                    {{-- Eraser Properties --}}
                    <div x-show="activeTool === 'eraser'" class="space-y-3">
                        <h4 class="font-semibold text-sm uppercase text-gray-400">Silgi Ayarları</h4>

                        <div>
                            <label class="block text-sm mb-1">Boyut</label>
                            <input type="range"
                                   x-model="brushSize"
                                   min="1"
                                   max="100"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="brushSize + 'px'"></div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Opaklık</label>
                            <input type="range"
                                   x-model="brushOpacity"
                                   min="1"
                                   max="100"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="brushOpacity + '%'"></div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Sertlik</label>
                            <input type="range"
                                   x-model="brushHardness"
                                   min="0"
                                   max="100"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="brushHardness + '%'"></div>
                        </div>
                    </div>

                    {{-- Text Properties - Advanced --}}
                    <div x-show="activeTool === 'text'" class="space-y-3 overflow-y-auto max-h-[calc(100vh-200px)]">
                        <h4 class="font-semibold text-xs uppercase text-gray-400 sticky top-0 bg-gray-800 py-1.5 mb-2">Hazır Başlık Stilleri</h4>

                        {{-- Text Templates --}}
                        <div class="grid grid-cols-2 gap-2 border-b border-gray-700 pb-3">
                            <template x-for="(template, index) in textTemplates" :key="index">
                                <button @click="if (activeTextIndex !== null) { applyTemplate(index); } else { selectedTemplate = JSON.parse(JSON.stringify(template)); }"
                                        class="rounded border-2 transition-all hover:border-blue-500 relative overflow-hidden"
                                        :class="(activeTextIndex !== null && textObjects[activeTextIndex] && textObjects[activeTextIndex].backgroundColor === template.backgroundColor && textObjects[activeTextIndex].color === template.textColor) || (selectedTemplate && selectedTemplate.backgroundColor === template.backgroundColor && selectedTemplate.textColor === template.textColor) ? 'border-blue-500 bg-blue-900/20' : 'border-gray-600 bg-gray-700/50'"
                                        :style="`background-color: ${template.backgroundColor === 'transparent' ? 'rgba(0,0,0,0.1)' : template.backgroundColor}; padding: ${Math.max(template.padding * 0.4, 4)}px; min-height: 50px; height: 50px; display: flex; align-items: center; justify-content: center;`">
                                    <span class="text-center overflow-hidden"
                                         :style="`color: ${template.textColor}; font-size: ${Math.min(template.fontSize * 0.4, 14)}px; font-family: ${template.fontFamily}; font-weight: ${template.fontWeight}; letter-spacing: ${template.letterSpacing * 0.3}px; ${template.textShadow.enabled ? 'text-shadow: ' + (template.textShadow.offsetX * 0.3) + 'px ' + (template.textShadow.offsetY * 0.3) + 'px ' + (template.textShadow.blur * 0.3) + 'px ' + template.textShadow.color + ';' : ''} line-height: 1.1; white-space: nowrap;`">
                                        Başlık
                                    </span>
                                </button>
                            </template>
                        </div>

                        <h4 class="font-semibold text-sm uppercase text-gray-400 sticky top-0 bg-gray-800 py-2">Metin Ayarları</h4>

                        {{-- Basic Text Properties --}}
                        <div class="space-y-3 border-b border-gray-700 pb-3">
                            <div x-show="activeTextIndex !== null && textObjects[activeTextIndex]">
                                <label class="block text-sm mb-1">Metin</label>
                                <input type="text"
                                       :value="activeTextIndex !== null && textObjects[activeTextIndex] ? textObjects[activeTextIndex].text : ''"
                                       @input="if (activeTextIndex !== null && textObjects[activeTextIndex]) { textObjects[activeTextIndex].text = $event.target.value; draw(); saveState(); }"
                                       @focus="if (activeTextIndex !== null && textObjects[activeTextIndex]) editingText = true;"
                                       @blur="if (activeTextIndex !== null && textObjects[activeTextIndex]) editingText = false;"
                                       placeholder="Metin girin..."
                                       class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm">
                                <p class="text-xs text-gray-400 mt-1">Yazıyı düzenlemek için input'a tıklayın veya canvas'ta yazıya çift tıklayın</p>
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Yazı Rengi</label>
                                <input type="color"
                                       :value="activeTextIndex !== null && textObjects[activeTextIndex] ? (textObjects[activeTextIndex].color || textColor) : textColor"
                                       @change="changeTextColor($event.target.value)"
                                       class="w-full h-10 rounded">
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Arka Plan Rengi</label>
                                <div class="flex gap-2">
                                    <input type="color"
                                           :value="activeTextIndex !== null && textObjects[activeTextIndex] ? (textObjects[activeTextIndex].backgroundColor && textObjects[activeTextIndex].backgroundColor !== 'transparent' ? textObjects[activeTextIndex].backgroundColor : '#000000') : (textBackgroundColor !== 'transparent' ? textBackgroundColor : '#000000')"
                                           @change="changeTextBackgroundColor($event.target.value)"
                                           class="flex-1 h-10 rounded">
                                    <button @click="changeTextBackgroundColor('transparent')"
                                            class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-xs"
                                            title="Şeffaf">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Font Boyutu</label>
                                <input type="range"
                                       x-model="fontSize"
                                       @input="changeFontSize($event.target.value)"
                                       min="8"
                                       max="200"
                                       class="w-full">
                                <div class="text-xs text-gray-400 text-right" x-text="fontSize + 'px'"></div>
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Font Ailesi</label>
                                <select x-model="fontFamily"
                                        @change="changeFontFamily($event.target.value)"
                                        class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm">
                                    <option value="Arial">Arial</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Courier New">Courier New</option>
                                    <option value="Verdana">Verdana</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Palatino">Palatino</option>
                                    <option value="Garamond">Garamond</option>
                                    <option value="Impact">Impact</option>
                                    <option value="Comic Sans MS">Comic Sans MS</option>
                                    <option value="Trebuchet MS">Trebuchet MS</option>
                                    <option value="Lucida Console">Lucida Console</option>
                                    <option value="Tahoma">Tahoma</option>
                                    <option value="Century Gothic">Century Gothic</option>
                                    <option value="Book Antiqua">Book Antiqua</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Font Ağırlığı</label>
                                <select x-model="fontWeight"
                                        @change="updateTextProperty('fontWeight', $event.target.value)"
                                        class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm">
                                    <option value="normal">Normal</option>
                                    <option value="100">100 - Thin</option>
                                    <option value="200">200 - Extra Light</option>
                                    <option value="300">300 - Light</option>
                                    <option value="400">400 - Regular</option>
                                    <option value="500">500 - Medium</option>
                                    <option value="600">600 - Semi Bold</option>
                                    <option value="700">700 - Bold</option>
                                    <option value="800">800 - Extra Bold</option>
                                    <option value="900">900 - Black</option>
                                </select>
                            </div>
                        </div>

                        {{-- Text Style Buttons --}}
                        <div class="space-y-2 border-b border-gray-700 pb-3">
                            <label class="block text-sm mb-1">Stil</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button @click="textBold = !textBold; updateTextProperty('textBold', textBold)"
                                        :class="textBold ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="Kalın">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button @click="textItalic = !textItalic; updateTextProperty('textItalic', textItalic)"
                                        :class="textItalic ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="İtalik">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button @click="textUnderline = !textUnderline; updateTextProperty('textUnderline', textUnderline)"
                                        :class="textUnderline ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="Altı Çizili">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <button @click="textStrikethrough = !textStrikethrough; updateTextProperty('textStrikethrough', textStrikethrough)"
                                        :class="textStrikethrough ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="Üstü Çizili">
                                    <i class="fas fa-strikethrough"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Text Alignment --}}
                        <div class="space-y-2 border-b border-gray-700 pb-3">
                            <label class="block text-sm mb-1">Hizalama</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button @click="textAlign = 'left'; updateTextProperty('textAlign', 'left')"
                                        :class="textAlign === 'left' ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="Sol">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button @click="textAlign = 'center'; updateTextProperty('textAlign', 'center')"
                                        :class="textAlign === 'center' ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="Orta">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button @click="textAlign = 'right'; updateTextProperty('textAlign', 'right')"
                                        :class="textAlign === 'right' ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="Sağ">
                                    <i class="fas fa-align-right"></i>
                                </button>
                                <button @click="textAlign = 'justify'; updateTextProperty('textAlign', 'justify')"
                                        :class="textAlign === 'justify' ? 'bg-blue-600' : 'bg-gray-700'"
                                        class="px-3 py-2 rounded text-sm hover:bg-gray-600"
                                        title="İki Yana">
                                    <i class="fas fa-align-justify"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Text Transform --}}
                        <div class="space-y-2 border-b border-gray-700 pb-3">
                            <label class="block text-sm mb-1">Dönüştür</label>
                            <select x-model="textTransform"
                                    @change="updateTextProperty('textTransform', $event.target.value)"
                                    class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm">
                                <option value="none">Normal</option>
                                <option value="uppercase">BÜYÜK HARF</option>
                                <option value="lowercase">küçük harf</option>
                                <option value="capitalize">Baş Harfler Büyük</option>
                            </select>
                        </div>

                        {{-- Letter Spacing & Line Height --}}
                        <div class="space-y-3 border-b border-gray-700 pb-3">
                            <div>
                                <label class="block text-sm mb-1">Harf Aralığı</label>
                                <input type="range"
                                       x-model="letterSpacing"
                                       @input="updateTextProperty('letterSpacing', parseFloat($event.target.value))"
                                       min="-5"
                                       max="20"
                                       step="0.1"
                                       class="w-full">
                                <div class="text-xs text-gray-400 text-right" x-text="letterSpacing + 'px'"></div>
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Satır Yüksekliği</label>
                                <input type="range"
                                       x-model="lineHeight"
                                       @input="updateTextProperty('lineHeight', parseFloat($event.target.value))"
                                       min="0.5"
                                       max="3"
                                       step="0.1"
                                       class="w-full">
                                <div class="text-xs text-gray-400 text-right" x-text="(parseFloat(lineHeight) || 1.2).toFixed(1)"></div>
                            </div>
                        </div>

                        {{-- Text Shadow --}}
                        <div class="space-y-3 border-b border-gray-700 pb-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm">Gölge</label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox"
                                           x-model="textShadow.enabled"
                                           @change="updateTextShadow('enabled', $event.target.checked)"
                                           class="rounded">
                                    <span class="text-xs">Aktif</span>
                                </label>
                            </div>
                            <div x-show="textShadow.enabled" class="space-y-2">
                                <div>
                                    <label class="block text-xs mb-1">Gölge Rengi</label>
                                    <input type="color"
                                           x-model="textShadow.color"
                                           @change="updateTextShadow('color', $event.target.value)"
                                           class="w-full h-8 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">Blur</label>
                                    <input type="range"
                                           x-model="textShadow.blur"
                                           @input="updateTextShadow('blur', parseFloat($event.target.value))"
                                           min="0"
                                           max="20"
                                           class="w-full">
                                    <div class="text-xs text-gray-400 text-right" x-text="textShadow.blur + 'px'"></div>
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">X Offset</label>
                                    <input type="range"
                                           x-model="textShadow.offsetX"
                                           @input="updateTextShadow('offsetX', parseFloat($event.target.value))"
                                           min="-20"
                                           max="20"
                                           class="w-full">
                                    <div class="text-xs text-gray-400 text-right" x-text="textShadow.offsetX + 'px'"></div>
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">Y Offset</label>
                                    <input type="range"
                                           x-model="textShadow.offsetY"
                                           @input="updateTextShadow('offsetY', parseFloat($event.target.value))"
                                           min="-20"
                                           max="20"
                                           class="w-full">
                                    <div class="text-xs text-gray-400 text-right" x-text="textShadow.offsetY + 'px'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Text Stroke --}}
                        <div class="space-y-3 border-b border-gray-700 pb-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm">Kontur</label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox"
                                           x-model="textStroke.enabled"
                                           @change="updateTextStroke('enabled', $event.target.checked)"
                                           class="rounded">
                                    <span class="text-xs">Aktif</span>
                                </label>
                            </div>
                            <div x-show="textStroke.enabled" class="space-y-2">
                                <div>
                                    <label class="block text-xs mb-1">Kontur Rengi</label>
                                    <input type="color"
                                           x-model="textStroke.color"
                                           @change="updateTextStroke('color', $event.target.value)"
                                           class="w-full h-8 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1">Kontur Kalınlığı</label>
                                    <input type="range"
                                           x-model="textStroke.width"
                                           @input="updateTextStroke('width', parseFloat($event.target.value))"
                                           min="1"
                                           max="10"
                                           class="w-full">
                                    <div class="text-xs text-gray-400 text-right" x-text="textStroke.width + 'px'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Edit Text Button --}}
                        <div x-show="activeTextIndex !== null">
                            <button @click="editText()"
                                    class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-sm"
                                    title="Metni düzenle (çift tıklayın)">
                                <i class="fas fa-edit mr-1"></i> Metni Düzenle
                            </button>
                        </div>
                    </div>


                    {{-- Zoom Control --}}
                    <div class="space-y-3 border-t border-gray-700 pt-4">
                        <h4 class="font-semibold text-sm uppercase text-gray-400">Zoom</h4>

                        <div>
                            <label class="block text-sm mb-1">Yakınlaştır/Uzaklaştır</label>
                            <input type="range"
                                   :value="Math.round(zoom * 100)"
                                   @input="setZoomFromPercent($event.target.value)"
                                   min="10"
                                   max="1000"
                                   step="1"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="Math.round(zoom * 100) + '%'"></div>
                        </div>

                        <button @click="resetZoom()"
                                class="w-full px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                title="Zoom'u %100'e sıfırla">
                            <i class="fas fa-undo mr-1"></i> Zoom Sıfırla
                        </button>
                    </div>

                    {{-- Image Adjustments --}}
                    <div class="space-y-3 border-t border-gray-700 pt-4">
                        <h4 class="font-semibold text-sm uppercase text-gray-400">Görüntü Ayarları</h4>

                        <div>
                            <label class="block text-sm mb-1">Parlaklık</label>
                            <input type="range"
                                   x-model="brightness"
                                   @input="adjustFilter('brightness', $event.target.value)"
                                   min="0"
                                   max="200"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="brightness + '%'"></div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Kontrast</label>
                            <input type="range"
                                   x-model="contrast"
                                   @input="adjustFilter('contrast', $event.target.value)"
                                   min="0"
                                   max="200"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="contrast + '%'"></div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Doygunluk</label>
                            <input type="range"
                                   x-model="saturation"
                                   @input="adjustFilter('saturation', $event.target.value)"
                                   min="0"
                                   max="200"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="saturation + '%'"></div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Ton</label>
                            <input type="range"
                                   x-model="hue"
                                   @input="adjustFilter('hue', $event.target.value)"
                                   min="-180"
                                   max="180"
                                   class="w-full">
                            <div class="text-xs text-gray-400 text-right" x-text="hue + '°'"></div>
                        </div>

                        <button @click="resetFilters()"
                                class="w-full px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                title="Tüm görüntü ayarlarını sıfırla">
                            <i class="fas fa-undo mr-1"></i> Ayarları Sıfırla
                        </button>
                    </div>

                    {{-- Transform Tools --}}
                    <div class="space-y-3 border-t border-gray-700 pt-4">
                        <h4 class="font-semibold text-sm uppercase text-gray-400">Dönüştür</h4>

                        <div class="grid grid-cols-2 gap-2">
                            <button @click="rotate(-90)"
                                    class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                    title="Saat yönünün tersine 90° döndür">
                                <i class="fas fa-redo"></i> -90°
                            </button>
                            <button @click="rotate(90)"
                                    class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                    title="Saat yönünde 90° döndür">
                                <i class="fas fa-undo"></i> +90°
                            </button>
                            <button @click="flipHorizontal()"
                                    class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                    title="Yatay olarak çevir">
                                <i class="fas fa-arrows-alt-h"></i> Yatay
                            </button>
                            <button @click="flipVertical()"
                                    class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                    title="Dikey olarak çevir">
                                <i class="fas fa-arrows-alt-v"></i> Dikey
                            </button>
                        </div>
                    </div>

                    {{-- Crop Actions --}}
                    <div x-show="activeTool === 'crop' && isCropping"
                         class="space-y-3 border-t border-gray-700 pt-4">
                        <button @click="applyCrop()"
                                class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 rounded text-sm"
                                title="Kırpmayı uygula (Enter)">
                            <i class="fas fa-check"></i> Kırpmayı Uygula
                        </button>
                        <button @click="isCropping = false; draw()"
                                class="w-full px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm"
                                title="Kırpmayı iptal et (Escape)">
                            <i class="fas fa-times"></i> İptal
                        </button>
                    </div>

                    {{-- Delete Selected --}}
                    <div x-show="activeTextIndex !== null"
                         class="border-t border-gray-700 pt-4">
                        <button @click="deleteSelected()"
                                class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 rounded text-sm"
                                title="Seçili öğeyi sil (Delete)">
                            <i class="fas fa-trash"></i> Seçili Öğeyi Sil
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Bottom Status Bar --}}
        <div class="px-4 py-2 bg-gray-800 border-t border-gray-700 flex items-center justify-between text-xs">
            <div class="flex items-center space-x-4">
                <span>Zoom: <span x-text="Math.round(zoom * 100) + '%'"></span></span>
                <span x-text="'Boyut: ' + Math.round(canvasWidth) + ' × ' + Math.round(canvasHeight) + 'px'"></span>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="saveEditedImage()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded text-sm font-medium"
                        title="Düzenlenmiş resmi kaydet">
                    <i class="fas fa-save"></i> Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

