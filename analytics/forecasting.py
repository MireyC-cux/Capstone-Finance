#!/usr/bin/env python3
import sys
import json
import os
import numpy as np
import pandas as pd
import mysql.connector
from sklearn.linear_model import LinearRegression
from sklearn.metrics import r2_score
from datetime import datetime

def safe_json_output(data):
    try:
        print(json.dumps(data, ensure_ascii=False))
    except Exception as e:
        print(json.dumps({"error": f"JSON encoding failed: {str(e)}"}))

# ===== CONFIG =====
months_ahead = 3
if len(sys.argv) >= 2:
    try:
        months_ahead = int(sys.argv[1])
    except:
        months_ahead = 3

# DB config via argv or env, with sane defaults
db_host = '127.0.0.1'
db_user = 'root'
db_pass = ''
db_name = 'sacstms'

if len(sys.argv) >= 6:
    db_host = sys.argv[2]
    db_user = sys.argv[3]
    db_pass = sys.argv[4]
    db_name = sys.argv[5]
else:
    db_host = os.environ.get('DB_HOST', db_host)
    db_user = os.environ.get('DB_USERNAME', db_user)
    db_pass = os.environ.get('DB_PASSWORD', db_pass)
    db_name = os.environ.get('DB_DATABASE', db_name)

# ===== DATABASE CONNECTION =====
try:
    db = mysql.connector.connect(
        host=db_host,
        user=db_user,
        password=db_pass,
        database=db_name
    )
    cursor = db.cursor(dictionary=True)

    # Aggregate monthly inflows and outflows from cash_flow table
    cursor.execute(
        """
        SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS month,
               SUM(CASE WHEN transaction_type = 'Inflow' THEN amount ELSE 0 END) AS inflow,
               SUM(CASE WHEN transaction_type = 'Outflow' THEN amount ELSE 0 END) AS outflow
        FROM cash_flow
        GROUP BY month
        ORDER BY month ASC
        """
    )
    cash_rows = cursor.fetchall()

    cursor.close()
    db.close()

except mysql.connector.Error as err:
    safe_json_output({"error": f"Database error: {err}"})
    sys.exit(0)
except Exception as e:
    safe_json_output({"error": f"General error: {e}"})
    sys.exit(0)

# ===== FORECASTING FUNCTION =====
def forecast_metric(rows, months_ahead=3):
    df = pd.DataFrame(rows)
    if df.empty:
        return {"months": [], "values": [], "trend": "none", "growth_rate": 0.0, "confidence": 0.0}

    df["month"] = pd.to_datetime(df["month"].astype(str) + "-01")
    df = df.sort_values("month").reset_index(drop=True)

    full_range = pd.date_range(start=df["month"].min(), end=df["month"].max(), freq='MS')
    df = df.set_index("month").reindex(full_range, fill_value=0).rename_axis("month").reset_index()

    df["value"] = df["value"].astype(float)
    y = df["value"].values
    X = np.arange(1, len(y) + 1).reshape(-1, 1)

    if len(y) >= 3:
        y_smooth = np.convolve(y, np.ones(3)/3, mode='same')
    else:
        y_smooth = y

    if len(y_smooth) < 2 or np.allclose(y_smooth, 0):
        last_val = float(y_smooth[-1]) if len(y_smooth) else 0.0
        preds = [last_val] * months_ahead
        confidence = 0.0
    else:
        model = LinearRegression().fit(X, y_smooth)
        future_X = np.arange(len(y_smooth) + 1, len(y_smooth) + months_ahead + 1).reshape(-1, 1)
        preds = model.predict(future_X)
        preds = [max(0, round(float(p), 2)) for p in preds]
        confidence = round(float(r2_score(y_smooth, model.predict(X))), 2) if len(y_smooth) > 2 else 0.0

    hist_avg = np.mean(y_smooth[-3:]) if len(y_smooth) >= 3 else np.mean(y_smooth)
    forecast_avg = np.mean(preds)
    growth_rate = 0.0 if hist_avg == 0 else (forecast_avg - hist_avg) / hist_avg
    trend = "rising" if growth_rate > 0.1 else "falling" if growth_rate < -0.1 else "stable"

    hist_months = df["month"].dt.strftime("%b %Y").tolist()
    hist_values = [round(float(v), 2) for v in df["value"].tolist()]
    last_month = df["month"].iloc[-1]
    future_months = [(last_month + pd.DateOffset(months=i)).strftime("%b %Y") for i in range(1, months_ahead + 1)]

    return {
        "months": hist_months + future_months,
        "values": hist_values + preds,
        "trend": trend,
        "growth_rate": round(growth_rate, 3),
        "confidence": confidence
    }

# ===== PREPARE SERIES =====
def build_series(rows, key):
    out = []
    for r in rows:
        try:
            out.append({"month": r["month"], "value": float(r.get(key) or 0)})
        except Exception:
            out.append({"month": r["month"], "value": 0.0})
    return out

inflow_rows = build_series(cash_rows, "inflow")
outflow_rows = build_series(cash_rows, "outflow")
profit_rows = []
for r in cash_rows:
    infl = float(r.get("inflow") or 0)
    outl = float(r.get("outflow") or 0)
    profit_rows.append({"month": r["month"], "value": infl - outl})

# ===== RUN FORECASTS =====
inflows = forecast_metric(inflow_rows, months_ahead)
outflows = forecast_metric(outflow_rows, months_ahead)
profits = forecast_metric(profit_rows, months_ahead)

# ===== GENERATE INSIGHT =====
try:
    # Determine split between history and forecast for profit
    total_len = len(profits.get("values", []))
    hist_len = max(0, total_len - months_ahead)
    hist_vals = profits.get("values", [])[:hist_len]
    future_vals = profits.get("values", [])[-months_ahead:] if total_len >= months_ahead else []
    future_months = profits.get("months", [])[-months_ahead:] if total_len >= months_ahead else []

    hist_avg = np.mean(hist_vals[-3:]) if len(hist_vals) >= 3 else (np.mean(hist_vals) if hist_vals else 0)
    forecast_avg = np.mean(future_vals) if future_vals else 0
    growth_pct = 0.0 if hist_avg == 0 else (forecast_avg - hist_avg) / hist_avg
    trend_word = "improve" if growth_pct > 0.05 else "decline" if growth_pct < -0.05 else "remain steady"

    bullets = []
    for m, v in zip(future_months, future_vals):
        bullets.append(f"{m}: ₱{float(v):,.2f}")

    insight_text = (
        "Sales performance outlook: Profit is expected to "
        f"{trend_word} over the next {months_ahead} months (avg change: {growth_pct*100:.1f}%). "
        + (" | Forecast by month → " + "; ".join(bullets) if bullets else "")
    )
except Exception as e:
    insight_text = f"Unable to generate forecast insight: {str(e)}"

# ===== FINAL JSON OUTPUT =====
# Split months into history and forecast parts using inflows timeline (all are aligned)
def split_parts(obj):
    total = len(obj.get("months", []))
    hist_n = max(0, total - months_ahead)
    return {
        "history": obj.get("values", [])[:hist_n],
        "forecast": obj.get("values", [])[hist_n:],
        "trend": obj.get("trend", "none"),
        "growth_rate": obj.get("growth_rate", 0.0),
        "confidence": obj.get("confidence", 0.0)
    }

labels = inflows.get("months", [])
months_history = labels[:-months_ahead] if len(labels) > months_ahead else labels[:]
months_forecast = labels[-months_ahead:] if len(labels) >= months_ahead else []

safe_json_output({
    "months_history": months_history,
    "months_forecast": months_forecast,
    "inflow": split_parts(inflows),
    "outflow": split_parts(outflows),
    "profit": split_parts(profits),
    "insight": insight_text
})
