import React from 'react';
import { Head } from '@inertiajs/react';

export default function Index({ message, timestamp, user, features }) {
    return (
        <>
            <Head title="Página de Prueba - Inertia.js + React" />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl mx-auto">
                    {/* Header */}
                    <div className="text-center mb-12">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            🚀 Laravel + Inertia.js + React
                        </h1>
                        <p className="text-xl text-gray-600">
                            Integración exitosa completada
                        </p>
                    </div>

                    {/* Main Content Card */}
                    <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
                        {/* Message Section */}
                        <div className="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                            <h2 className="text-2xl font-bold text-white mb-2">
                                Mensaje desde Laravel
                            </h2>
                            <p className="text-blue-100 text-lg">
                                {message}
                            </p>
                        </div>

                        {/* Content Grid */}
                        <div className="p-8">
                            <div className="grid md:grid-cols-2 gap-8">
                                {/* Server Info */}
                                <div className="space-y-4">
                                    <h3 className="text-xl font-semibold text-gray-800 mb-4">
                                        📊 Información del Servidor
                                    </h3>
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <div className="flex justify-between items-center mb-2">
                                            <span className="text-gray-600">Timestamp:</span>
                                            <span className="font-mono text-sm text-gray-800">
                                                {new Date(timestamp).toLocaleString('es-ES')}
                                            </span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-gray-600">Usuario:</span>
                                            <span className="font-medium text-gray-800">
                                                {user ? user.name : 'No autenticado'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Features */}
                                <div className="space-y-4">
                                    <h3 className="text-xl font-semibold text-gray-800 mb-4">
                                        ✨ Características Integradas
                                    </h3>
                                    <div className="space-y-2">
                                        {features.map((feature, index) => (
                                            <div key={index} className="flex items-center space-x-3">
                                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span className="text-gray-700">{feature}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="mt-8 pt-8 border-t border-gray-200">
                                <div className="flex flex-wrap gap-4 justify-center">
                                    <button
                                        onClick={() => window.location.reload()}
                                        className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                                    >
                                        🔄 Recargar Página
                                    </button>
                                    <a
                                        href="/"
                                        className="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
                                    >
                                        🏠 Ir al Inicio
                                    </a>
                                    <a
                                        href="/api-status"
                                        className="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                                    >
                                        📡 Ver API Status
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="text-center mt-8 text-gray-500">
                        <p>Próximo paso: Migrar componentes shadcn/ui y módulos del frontend existente</p>
                    </div>
                </div>
            </div>
        </>
    );
}
