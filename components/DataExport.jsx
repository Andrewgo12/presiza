"use client"

import { useState } from "react"
import { Download, FileText, Database, Globe, X, CheckCircle } from "lucide-react"

const DataExport = ({ isOpen, onClose }) => {
  const [exportConfig, setExportConfig] = useState({
    dataType: "all",
    format: "json",
    dateRange: "30",
    includeMetadata: true,
    compression: false,
    customDateRange: {
      start: "",
      end: "",
    },
    filters: {
      status: "all",
      groups: [],
      users: [],
    },
  })
  const [exporting, setExporting] = useState(false)
  const [progress, setProgress] = useState(0)

  const dataTypes = [
    {
      id: "all",
      name: "Todos los Datos",
      description: "Exportar toda la información del sistema",
      icon: Database,
      color: "blue",
      estimatedSize: "~50MB",
    },
    {
      id: "files",
      name: "Archivos y Evidencias",
      description: "Metadatos de archivos, evidencias y evaluaciones",
      icon: FileText,
      color: "green",
      estimatedSize: "~15MB",
    },
    {
      id: "users",
      name: "Datos de Usuarios",
      description: "Información de usuarios, grupos y actividad",
      icon: Globe,
      color: "purple",
      estimatedSize: "~5MB",
    },
    {
      id: "analytics",
      name: "Datos Analíticos",
      description: "Métricas, estadísticas y reportes del sistema",
      icon: CheckCircle,
      color: "orange",
      estimatedSize: "~10MB",
    },
  ]

  const formatOptions = [
    { id: "json", name: "JSON", description: "Formato JavaScript Object Notation" },
    { id: "csv", name: "CSV", description: "Valores separados por comas" },
    { id: "xml", name: "XML", description: "Lenguaje de marcado extensible" },
    { id: "excel", name: "Excel", description: "Hoja de cálculo de Microsoft Excel" },
  ]

  const handleExport = async () => {
    setExporting(true)
    setProgress(0)

    // Simular proceso de exportación
    const steps = [
      "Recopilando datos...",
      "Procesando información...",
      "Aplicando filtros...",
      "Generando archivo...",
      "Finalizando exportación...",
    ]

    for (let i = 0; i < steps.length; i++) {
      await new Promise((resolve) => setTimeout(resolve, 1000))
      setProgress(((i + 1) / steps.length) * 100)
    }

    // Crear datos mock para exportación
    const exportData = {
      metadata: {
        exportedAt: new Date().toISOString(),
        dataType: exportConfig.dataType,
        format: exportConfig.format,
        dateRange: exportConfig.dateRange,
        totalRecords: 1247,
        version: "1.0.0",
      },
      data: {
        files: [
          {
            id: 1,
            name: "Research_Analysis.pdf",
            author: "Dr. Smith",
            uploadDate: "2024-01-15T10:30:00Z",
            status: "approved",
            size: 2048576,
          },
        ],
        users: [
          {
            id: 1,
            name: "Dr. Smith",
            email: "dr.smith@company.com",
            role: "admin",
            joinDate: "2023-06-15T10:00:00Z",
          },
        ],
        groups: [
          {
            id: 1,
            name: "Research Team Alpha",
            type: "public",
            members: 24,
            createdDate: "2023-12-01T10:00:00Z",
          },
        ],
      },
    }

    // Simular descarga de archivo
    const blob = new Blob([JSON.stringify(exportData, null, 2)], {
      type: getContentType(exportConfig.format),
    })
    const url = URL.createObjectURL(blob)
    const a = document.createElement("a")
    a.href = url
    a.download = `data_export_${new Date().toISOString().split("T")[0]}.${exportConfig.format}`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)

    // Mostrar notificación de éxito
    window.dispatchEvent(
      new CustomEvent("showNotification", {
        detail: {
          type: "success",
          title: "Exportación Completada",
          message: `Los datos han sido exportados exitosamente en formato ${exportConfig.format.toUpperCase()}`,
          duration: 5000,
        },
      }),
    )

    setExporting(false)
    setProgress(0)
    onClose()
  }

  const getContentType = (format) => {
    switch (format) {
      case "json":
        return "application/json"
      case "csv":
        return "text/csv"
      case "xml":
        return "application/xml"
      case "excel":
        return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      default:
        return "application/octet-stream"
    }
  }

  const handleConfigChange = (key, value) => {
    setExportConfig((prev) => ({
      ...prev,
      [key]: value,
    }))
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto animate-scale-in">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Exportar Datos</h2>
              <p className="text-sm text-gray-600 mt-1">Exportar datos del sistema en múltiples formatos</p>
            </div>
            <button
              onClick={onClose}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              disabled={exporting}
            >
              <X className="w-5 h-5 text-gray-500" />
            </button>
          </div>
        </div>

        <div className="p-6 space-y-6">
          {/* Tipo de Datos */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-4">Tipo de Datos</label>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {dataTypes.map((type) => {
                const Icon = type.icon
                return (
                  <button
                    key={type.id}
                    onClick={() => handleConfigChange("dataType", type.id)}
                    disabled={exporting}
                    className={`p-4 border-2 rounded-lg text-left transition-all ${
                      exportConfig.dataType === type.id
                        ? `border-${type.color}-500 bg-${type.color}-50`
                        : "border-gray-200 hover:border-gray-300 hover:bg-gray-50"
                    } ${exporting ? "opacity-50 cursor-not-allowed" : ""}`}
                  >
                    <div className="flex items-start space-x-3">
                      <div
                        className={`p-2 rounded-lg ${
                          exportConfig.dataType === type.id ? `bg-${type.color}-100` : "bg-gray-100"
                        }`}
                      >
                        <Icon
                          className={`w-5 h-5 ${
                            exportConfig.dataType === type.id ? `text-${type.color}-600` : "text-gray-600"
                          }`}
                        />
                      </div>
                      <div className="flex-1">
                        <h3 className="font-medium text-gray-900">{type.name}</h3>
                        <p className="text-sm text-gray-500 mt-1">{type.description}</p>
                        <p className="text-xs text-gray-400 mt-2">{type.estimatedSize}</p>
                      </div>
                    </div>
                  </button>
                )
              })}
            </div>
          </div>

          {/* Formato y Rango de Fechas */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Formato de Exportación</label>
              <select
                value={exportConfig.format}
                onChange={(e) => handleConfigChange("format", e.target.value)}
                disabled={exporting}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
              >
                {formatOptions.map((format) => (
                  <option key={format.id} value={format.id}>
                    {format.name} - {format.description}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Rango de Fechas</label>
              <select
                value={exportConfig.dateRange}
                onChange={(e) => handleConfigChange("dateRange", e.target.value)}
                disabled={exporting}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
              >
                <option value="7">Últimos 7 días</option>
                <option value="30">Últimos 30 días</option>
                <option value="90">Últimos 90 días</option>
                <option value="365">Último año</option>
                <option value="all">Todos los datos</option>
                <option value="custom">Rango personalizado</option>
              </select>
            </div>
          </div>

          {/* Rango Personalizado */}
          {exportConfig.dateRange === "custom" && (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Fecha de Inicio</label>
                <input
                  type="date"
                  value={exportConfig.customDateRange.start}
                  onChange={(e) =>
                    setExportConfig((prev) => ({
                      ...prev,
                      customDateRange: { ...prev.customDateRange, start: e.target.value },
                    }))
                  }
                  disabled={exporting}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Fecha de Fin</label>
                <input
                  type="date"
                  value={exportConfig.customDateRange.end}
                  onChange={(e) =>
                    setExportConfig((prev) => ({
                      ...prev,
                      customDateRange: { ...prev.customDateRange, end: e.target.value },
                    }))
                  }
                  disabled={exporting}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                />
              </div>
            </div>
          )}

          {/* Opciones Adicionales */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-4">Opciones Adicionales</label>
            <div className="space-y-3">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={exportConfig.includeMetadata}
                  onChange={(e) => handleConfigChange("includeMetadata", e.target.checked)}
                  disabled={exporting}
                  className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50"
                />
                <div>
                  <span className="text-sm font-medium text-gray-700">Incluir Metadatos</span>
                  <p className="text-xs text-gray-500">Agregar información adicional sobre la exportación</p>
                </div>
              </label>
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={exportConfig.compression}
                  onChange={(e) => handleConfigChange("compression", e.target.checked)}
                  disabled={exporting}
                  className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50"
                />
                <div>
                  <span className="text-sm font-medium text-gray-700">Comprimir Archivo</span>
                  <p className="text-xs text-gray-500">Reducir el tamaño del archivo usando compresión ZIP</p>
                </div>
              </label>
            </div>
          </div>

          {/* Barra de Progreso */}
          {exporting && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-2">
                <span className="text-sm font-medium text-blue-900">Exportando Datos...</span>
                <span className="text-sm text-blue-700">{Math.round(progress)}%</span>
              </div>
              <div className="w-full bg-blue-200 rounded-full h-2">
                <div
                  className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  style={{ width: `${progress}%` }}
                ></div>
              </div>
              <p className="text-xs text-blue-700 mt-2">Esto puede tomar unos momentos...</p>
            </div>
          )}

          {/* Vista Previa */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="font-medium text-gray-900 mb-3">Vista Previa de Exportación</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <span className="text-gray-600">Tipo:</span>
                <span className="ml-2 font-medium">{dataTypes.find((t) => t.id === exportConfig.dataType)?.name}</span>
              </div>
              <div>
                <span className="text-gray-600">Formato:</span>
                <span className="ml-2 font-medium uppercase">{exportConfig.format}</span>
              </div>
              <div>
                <span className="text-gray-600">Período:</span>
                <span className="ml-2 font-medium">
                  {exportConfig.dateRange === "custom"
                    ? "Personalizado"
                    : exportConfig.dateRange === "all"
                      ? "Todos"
                      : `${exportConfig.dateRange} días`}
                </span>
              </div>
              <div>
                <span className="text-gray-600">Metadatos:</span>
                <span className="ml-2 font-medium">{exportConfig.includeMetadata ? "Incluidos" : "Excluidos"}</span>
              </div>
            </div>
          </div>
        </div>

        <div className="p-6 border-t border-gray-200 bg-gray-50">
          <div className="flex justify-end space-x-3">
            <button
              onClick={onClose}
              disabled={exporting}
              className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Cancelar
            </button>
            <button
              onClick={handleExport}
              disabled={exporting}
              className="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {exporting ? (
                <>
                  <div className="spinner w-4 h-4 mr-2"></div>
                  Exportando...
                </>
              ) : (
                <>
                  <Download className="w-4 h-4 mr-2" />
                  Exportar Datos
                </>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default DataExport
