#!/usr/bin/env python3
"""
Advanced ML Analytics Engine for Pharmacy
Features:
1. Demand Forecasting (Time Series Analysis)
2. Anomaly Detection (Fraud Detection)
3. Expiry Risk Analysis
4. Staff Performance Analytics
"""
import sys
import json
from datetime import datetime, timedelta
from statistics import mean, stdev

# Fix encoding for Windows
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

class PharmacyMLEngine:
    def __init__(self, data):
        self.data = data
        self.drugs = data.get('drugs', [])
        self.sales = data.get('sales', [])
        
    def moving_average(self, values, window=7):
        """Calculate moving average"""
        if len(values) < window:
            return values[-1] if values else 0
        return mean(values[-window:])
    
    def exponential_smoothing(self, values, alpha=0.3):
        """Simple exponential smoothing for trend"""
        if not values:
            return 0
        result = values[0]
        for v in values[1:]:
            result = alpha * v + (1 - alpha) * result
        return result
    
    def detect_anomaly(self, value, values):
        """Z-score based anomaly detection"""
        if len(values) < 3:
            return False
        m = mean(values)
        std = stdev(values) if len(values) > 1 else 0
        if std == 0:
            return False
        z_score = abs((value - m) / std)
        return z_score > 2.5  # More than 2.5 std deviations
    
    def forecast_demand(self):
        """Forecast demand for next 7 days"""
        forecasts = []
        
        for drug in self.drugs:
            drug_id = drug['id']
            drug_name = drug['name']
            current_stock = drug.get('quantity', 0)
            
            # Get historical sales for this drug
            hist_sales = [s.get('qty', 0) for s in self.sales if s.get('drug_id') == drug_id]
            
            if not hist_sales or len(hist_sales) == 0:
                # No history - assume moderate demand
                forecasts.append({
                    'drug_id': drug_id,
                    'name': drug_name,
                    'current_stock': current_stock,
                    'avg_daily_sales': 0,
                    '7day_forecast': 10,
                    '14day_forecast': 20,
                    'confidence': 0.3,
                    'trend': 'neutral',
                    'recommendation': '✅ New drug - monitoring demand'
                })
                continue
            
            # Calculate trend
            avg_sales = mean(hist_sales)
            trend_val = self.exponential_smoothing(hist_sales)
            
            # Simple trend direction
            if len(hist_sales) >= 2:
                recent_avg = mean(hist_sales[-3:])
                older_avg = mean(hist_sales[:3]) if len(hist_sales) >= 3 else hist_sales[0]
                if recent_avg > older_avg * 1.2:
                    trend = 'increasing'
                elif recent_avg < older_avg * 0.8:
                    trend = 'decreasing'
                else:
                    trend = 'stable'
            else:
                trend = 'stable'
            
            # Forecast with trend
            forecast_7 = round(avg_sales * 7 * (1.1 if trend == 'increasing' else 0.9 if trend == 'decreasing' else 1.0))
            forecast_14 = round(avg_sales * 14 * (1.15 if trend == 'increasing' else 0.85 if trend == 'decreasing' else 1.0))
            
            # Confidence based on history length
            confidence = min(0.95, 0.3 + (len(hist_sales) * 0.05))
            
            forecasts.append({
                'drug_id': drug_id,
                'name': drug_name,
                'current_stock': drug.get('quantity', 0),
                'avg_daily_sales': round(avg_sales, 2),
                '7day_forecast': forecast_7,
                '14day_forecast': forecast_14,
                'confidence': round(confidence, 2),
                'trend': trend,
                'recommendation': self._forecast_recommendation(forecast_7, drug.get('quantity', 0))
            })
        
        return forecasts
    
    def _forecast_recommendation(self, forecast, current_stock):
        if current_stock < forecast * 0.3:
            return '🚨 URGENT: Stock critically low - reorder immediately'
        elif current_stock < forecast * 0.7:
            return '⚠️ WARNING: Consider reordering soon'
        elif current_stock > forecast * 2:
            return '✅ Good stock level'
        else:
            return '✅ Normal'
    
    def detect_fraud(self):
        """Detect anomalous transactions"""
        anomalies = []
        
        # Group sales by staff and drug
        staff_sales = {}
        for sale in self.sales:
            staff = sale.get('staff', 'Unknown')
            if staff not in staff_sales:
                staff_sales[staff] = []
            staff_sales[staff].append(sale.get('qty', 0))
        
        # Check for anomalies
        for staff, quantities in staff_sales.items():
            if not quantities or len(quantities) < 2:
                continue
                
            avg_qty = mean(quantities)
            qty_stdev = stdev(quantities)
            
            for i, qty in enumerate(quantities):
                if qty_stdev > 0 and self.detect_anomaly(qty, quantities):
                    deviation_pct = ((qty - avg_qty) / avg_qty * 100) if avg_qty > 0 else 0
                    anomalies.append({
                        'staff': staff,
                        'transaction': i,
                        'quantity': qty,
                        'avg_quantity': round(avg_qty, 1),
                        'deviation': round(abs(deviation_pct), 1),
                        'risk_level': 'high' if abs(deviation_pct) > 300 else 'medium'
                    })
        
        return anomalies
    
    def inventory_optimization(self):
        # Inventory optimization removed per user request. Keep placeholder return.
        return {'A': [], 'B': [], 'C': []}
    
    def price_optimization(self):
        # Price optimization removed per user request. Return empty list placeholder.
        return []

    def expiry_analysis(self):
        """Produce expiry-focused analytics per drug (days to expiry, risk score, recommendation)

        Uses similar heuristics as the lightweight expiry engine: days left, avg daily sales, and current
        quantity to compute a risk_score (0..1) and classification.
        """
        results = []
        today = datetime.utcnow().date()

        # Pre-compute avg daily sales per drug from provided sales (last 90 days)
        sales_by_drug = {}
        for s in self.sales:
            did = s.get('drug_id')
            sales_by_drug.setdefault(did, []).append(s.get('qty', 0))

        for d in self.drugs:
            try:
                exp_date_str = d.get('exp_date', '')
                exp_date = None
                if exp_date_str:
                    exp_date = datetime.strptime(exp_date_str, '%Y-%m-%d').date()
                    days_left = (exp_date - today).days
                else:
                    days_left = None
            except Exception:
                days_left = None

            hist = sales_by_drug.get(d.get('id'), [])
            avg_sales = round(mean(hist), 3) if hist else 0.0
            qty = d.get('quantity', 0) or 0

            # Compute risk similar to ml_expiry.py
            if days_left is None:
                dl = 0
            else:
                dl = days_left

            # days factor: closer to expiry increases risk (0..1)
            days_factor = max(0.0, min(1.0, (90 - dl) / 90)) if dl is not None else 1.0
            if qty <= 0:
                stock_factor = 1.0
            else:
                stock_factor = max(0.0, 1.0 - (avg_sales / (qty + 1)))

            score = 0.6 * days_factor + 0.4 * stock_factor
            score = max(0.0, min(1.0, score))

            if score >= 0.7:
                level = 'high'
            elif score >= 0.4:
                level = 'medium'
            else:
                level = 'low'

            if dl <= 0:
                recommendation = 'DISPOSE - EXPIRED'
            elif level == 'high':
                recommendation = 'PRIORITIZE SELL'
            elif level == 'medium':
                recommendation = 'PROMOTE / RUN DISCOUNT'
            else:
                recommendation = 'Normal'

            results.append({
                'id': d.get('id'),
                'name': d.get('name'),
                'days_left': dl if dl is not None else None,
                'quantity': qty,
                'avg_daily_sales': avg_sales,
                'risk_score': round(score, 3),
                'risk_level': level,
                'recommendation': recommendation
            })

        return results
    
    def staff_performance(self):
        """Analyze staff behavior"""
        staff_metrics = {}
        
        for sale in self.sales:
            staff = sale.get('staff', 'Unknown')
            if staff not in staff_metrics:
                staff_metrics[staff] = {
                    'transactions': 0,
                    'total_quantity': 0,
                    'total_revenue': 0,
                    'quantities': []
                }
            
            staff_metrics[staff]['transactions'] += 1
            qty = sale.get('qty', 0)
            staff_metrics[staff]['total_quantity'] += qty
            staff_metrics[staff]['quantities'].append(qty)
            staff_metrics[staff]['total_revenue'] += sale.get('revenue', 0)
        
        result = []
        for staff, metrics in staff_metrics.items():
            avg_qty = metrics['total_quantity'] / metrics['transactions'] if metrics['transactions'] > 0 else 0
            
            # Calculate consistency
            qty_stdev = stdev(metrics['quantities']) if len(metrics['quantities']) > 1 else 0
            consistency = 'High' if qty_stdev < avg_qty * 0.3 else 'Variable'
            
            result.append({
                'staff': staff,
                'transactions': metrics['transactions'],
                'avg_quantity_per_sale': round(avg_qty, 2),
                'total_quantity_sold': metrics['total_quantity'],
                'total_revenue': round(metrics['total_revenue'], 2),
                'performance_score': round(min(100, metrics['transactions'] * 10 + (metrics['total_revenue'] / 10)), 1),
                'consistency': consistency
            })
        
        return result


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Input file required'}))
        sys.exit(1)
    
    try:
        with open(sys.argv[1], 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)
    
    engine = PharmacyMLEngine(data)
    
    results = {
        'demand_forecast': engine.forecast_demand(),
        'anomalies': engine.detect_fraud(),
        'expiry_analysis': engine.expiry_analysis(),
        'staff_performance': engine.staff_performance(),
        'timestamp': datetime.now().isoformat()
    }
    
    print(json.dumps(results, ensure_ascii=False, indent=2))

if __name__ == '__main__':
    main()
