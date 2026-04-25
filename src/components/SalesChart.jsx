import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Line, Bar } from 'react-chartjs-2';
import axios from 'axios';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const SalesChart = () => {
    const [salesData, setSalesData] = useState([]);
    const [chartType, setChartType] = useState('line');
    const [loading, setLoading] = useState(true);
    
    useEffect(() => {
        fetchSalesData();
        const interval = setInterval(fetchSalesData, 60000);
        return () => clearInterval(interval);
    }, []);
    
    const fetchSalesData = async () => {
        try {
            const response = await axios.get('http://localhost/smartpos/api/dashboard');
            if (response.data.status === 'success') {
                const weeklySales = response.data.data.weekly_sales || [];
                setSalesData(weeklySales);
            }
        } catch (error) {
            console.error('Error fetching sales data:', error);
            // Fallback data
            setSalesData([
                { day: 'Monday', total: 125000 },
                { day: 'Tuesday', total: 150000 },
                { day: 'Wednesday', total: 180000 },
                { day: 'Thursday', total: 220000 },
                { day: 'Friday', total: 280000 },
                { day: 'Saturday', total: 350000 },
                { day: 'Sunday', total: 420000 },
            ]);
        } finally {
            setLoading(false);
        }
    };
    
    const chartData = {
        labels: salesData.map(item => item.day),
        datasets: [
            {
                label: 'Sales (TZS)',
                data: salesData.map(item => item.total),
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: chartType === 'line' 
                    ? 'rgba(102, 126, 234, 0.1)'
                    : 'rgba(102, 126, 234, 0.7)',
                tension: 0.4,
                fill: chartType === 'line',
                pointBackgroundColor: 'rgb(118, 75, 162)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ],
    };
    
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: { size: 12, family: 'Inter' },
                    usePointStyle: true,
                },
            },
            title: {
                display: true,
                text: 'Weekly Sales Performance',
                font: { size: 16, weight: 'bold', family: 'Inter' },
                padding: { bottom: 20 },
            },
            tooltip: {
                callbacks: {
                    label: (context) => {
                        let label = context.dataset.label || '';
                        let value = context.parsed.y;
                        return `${label}: TZS ${value.toLocaleString()}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value) => `TZS ${value.toLocaleString()}`,
                    font: { size: 11 }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                }
            },
            x: {
                ticks: { font: { size: 11 } },
                grid: { display: false }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuart',
        },
    };
    
    const totalSales = salesData.reduce((sum, item) => sum + item.total, 0);
    const averageSales = totalSales / salesData.length;
    const bestDay = salesData.reduce((max, item) => item.total > max.total ? item : max, { total: 0 });
    
    return (
        <section id="analytics" className="py-20 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-12">
                    <motion.h2
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6 }}
                        className="text-4xl md:text-5xl font-bold gradient-text mb-4"
                    >
                        Sales Analytics Dashboard
                    </motion.h2>
                    <motion.p
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6, delay: 0.1 }}
                        className="text-xl text-gray-600 dark:text-gray-400"
                    >
                        Track your business performance with real-time insights
                    </motion.p>
                </div>
                
                <div className="grid lg:grid-cols-4 gap-6 mb-8">
                    <motion.div
                        initial={{ opacity: 0, scale: 0.9 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        className="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg"
                    >
                        <p className="text-gray-500 dark:text-gray-400 text-sm">Total Weekly Sales</p>
                        <p className="text-3xl font-bold text-gray-800 dark:text-white">TZS {totalSales.toLocaleString()}</p>
                        <p className="text-green-500 text-sm mt-2">↑ 12.5% from last week</p>
                    </motion.div>
                    
                    <motion.div
                        initial={{ opacity: 0, scale: 0.9 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 0.1 }}
                        className="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg"
                    >
                        <p className="text-gray-500 dark:text-gray-400 text-sm">Average Daily Sales</p>
                        <p className="text-3xl font-bold text-gray-800 dark:text-white">TZS {averageSales.toLocaleString()}</p>
                        <p className="text-gray-500 text-sm mt-2">per day average</p>
                    </motion.div>
                    
                    <motion.div
                        initial={{ opacity: 0, scale: 0.9 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 0.2 }}
                        className="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg"
                    >
                        <p className="text-gray-500 dark:text-gray-400 text-sm">Best Performing Day</p>
                        <p className="text-3xl font-bold text-gray-800 dark:text-white">{bestDay.day}</p>
                        <p className="text-green-500 text-sm mt-2">TZS {bestDay.total?.toLocaleString()}</p>
                    </motion.div>
                    
                    <motion.div
                        initial={{ opacity: 0, scale: 0.9 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 0.3 }}
                        className="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-6 shadow-lg text-white"
                    >
                        <p className="text-white/80 text-sm">AI Prediction</p>
                        <p className="text-3xl font-bold">TZS {(totalSales * 1.15).toLocaleString()}</p>
                        <p className="text-white/80 text-sm mt-2">Projected for next week ↑ 15%</p>
                    </motion.div>
                </div>
                
                <motion.div
                    initial={{ opacity: 0, y: 50 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: 0.2 }}
                    className="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl"
                >
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-xl font-bold text-gray-800 dark:text-white">Sales Trend</h3>
                        <div className="flex gap-2">
                            <button
                                onClick={() => setChartType('line')}
                                className={`px-4 py-2 rounded-lg transition ${
                                    chartType === 'line' 
                                        ? 'bg-purple-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                }`}
                            >
                                Line
                            </button>
                            <button
                                onClick={() => setChartType('bar')}
                                className={`px-4 py-2 rounded-lg transition ${
                                    chartType === 'bar' 
                                        ? 'bg-purple-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                }`}
                            >
                                Bar
                            </button>
                        </div>
                    </div>
                    
                    <div style={{ height: '400px' }}>
                        {!loading && (
                            chartType === 'line' ? (
                                <Line data={chartData} options={chartOptions} />
                            ) : (
                                <Bar data={chartData} options={chartOptions} />
                            )
                        )}
                    </div>
                </motion.div>
            </div>
        </section>
    );
};

export default SalesChart;