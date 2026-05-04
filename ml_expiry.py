#!/usr/bin/env python3
"""
Lightweight Expiry ML Engine (no external dependencies)
Reads input JSON file path argument containing a list of drugs with fields:
- id, name, exp_date (YYYY-MM-DD), days_left (int), avg_daily_sales (float), quantity
Writes JSON predictions to stdout with fields:
- id, name, days_left, risk_score (0-1), risk_level (low/medium/high), recommendation
"""
import sys
import json
from datetime import datetime

def compute_risk(days_left, avg_sales, quantity):
    # Base risk from days left: closer to expiry => higher risk
    if days_left <= 0:
        return 1.0
    # days factor: map 0..90+ to 1..0
    days_factor = max(0.0, min(1.0, (90 - days_left) / 90))
    # sales factor: if low sales relative to quantity, higher risk
    sales_velocity = avg_sales if avg_sales is not None else 0.0
    if quantity <= 0:
        stock_factor = 1.0
    else:
        stock_factor = max(0.0, 1.0 - (sales_velocity / (quantity + 1)))
    # combine factors
    score = 0.6 * days_factor + 0.4 * stock_factor
    score = max(0.0, min(1.0, score))
    return score


def risk_level(score):
    if score >= 0.7:
        return 'high'
    if score >= 0.4:
        return 'medium'
    return 'low'


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Input file required'}))
        sys.exit(1)

    infile = sys.argv[1]
    try:
        with open(infile, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

    results = []
    today = datetime.utcnow().date()
    for d in data.get('drugs', []):
        # Ensure required fields
        days_left = d.get('days_left')
        if days_left is None:
            # compute from exp_date
            try:
                exp = datetime.strptime(d.get('exp_date',''), '%Y-%m-%d').date()
                days_left = (exp - today).days
            except Exception:
                days_left = 0
        avg_sales = d.get('avg_daily_sales', 0.0) or 0.0
        qty = d.get('quantity', 0) or 0
        score = compute_risk(days_left, avg_sales, qty)
        level = risk_level(score)
        rec = ''
        if days_left <= 0:
            rec = 'DISPOSE - EXPIRED'
        elif level == 'high':
            rec = 'PRIORITIZE SELL (FEFO)'
        elif level == 'medium':
            rec = 'PROMOTE / RUN DISCOUNT'
        else:
            rec = 'Normal'

        results.append({
            'id': d.get('id'),
            'name': d.get('name'),
            'days_left': days_left,
            'quantity': qty,
            'avg_daily_sales': avg_sales,
            'risk_score': round(score, 3),
            'risk_level': level,
            'recommendation': rec
        })

    print(json.dumps({'results': results}, ensure_ascii=False))

if __name__ == '__main__':
    main()
